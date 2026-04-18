<?php

namespace App\Services;

use App\Events\FileMergeEvent;
use App\Models\ImageUpload;
use App\Models\VideoUpload;
use App\Resources\ImagesResources;
use App\Resources\VideosResources;
use App\Traits\CommonTraits;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class CommonService
{
    use CommonTraits;
    protected S3Client $s3;
    protected string $bucket;

    public function __construct()
    {
        $this->bucket = config('AppConfig.wasabi_bucket');

        $this->s3 = new S3Client([
            'version'     => 'latest',
            'region'      => config('AppConfig.wasabi_region'),
            'endpoint'    => config('AppConfig.wasabi_endpoint'),
            'credentials' => [
                'key'    => config('AppConfig.wasabi_key'),
                'secret' => config('AppConfig.wasabi_secret'),
            ],
            'use_path_style_endpoint' => true,
            'http' => [
                'verify' => false,
            ],
        ]);
    }
    /**
     * @param $file
     * @param string $folder
     * @param string $title
     * @return array
     * @throws Exception
     */
    public function storeImage($file, $folder, $title): array
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        $key = "{$folder}/{$filename}_" . uniqid() . ".{$extension}";

        try {
            // Upload PRIVATE object
            $this->s3->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $key,
                'Body'        => fopen($file->getRealPath(), 'rb'),
                'ContentType' => $file->getMimeType(),
            ]);
            $image = ImageUpload::create([
                'title' => $title ?? null,
                'media_url' => $key
            ]);

            return [
                'object_key' => $key,
                'image' => $image
            ];
        } catch (AwsException $e) {
            throw new Exception($e->getAwsErrorMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } catch (QueryException $e) {
            throw new Exception('Database error' . $e->errorInfo[2] ?? $e->getMessage());
        }
    }
    /**
     * @param  $request
     * @return array
     * @throws Exception
     */
    public function storeVideo($request): array
    {
        try {
            $uploadId    = $request->upload_id;
            $chunkIndex  = $request->chunk_index;
            $totalChunks = $request->total_chunks;
            $thumbnailId = $request->thumbnail_id;
            $file = $request->file;
            $chunkDir = storage_path("app/chunks/{$uploadId}");
            if (!File::exists($chunkDir)) {
                File::makeDirectory($chunkDir, 0755, true);
            }
            $file->move(
                $chunkDir,
                "chunk_{$chunkIndex}"
            );
            if ($this->allChunksUploaded($chunkDir, $totalChunks)) {
                $finalPath = storage_path("app/zips/{$uploadId}.zip");
                event(new FileMergeEvent(
                    fileName: "{$uploadId}.zip",
                    finalPath: $finalPath,
                    chunckDir: $chunkDir,
                    thumbnailId: $thumbnailId
                ));
                return [
                    'status' => 'completed',
                    'file'   => "{$uploadId}.zip"
                ];
            }

            return [
                'status' => 'uploading',
                'chunk'  => $chunkIndex
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } catch (QueryException $e) {
            throw new Exception('Database error' . $e->errorInfo[2] ?? $e->getMessage());
        }
    }
    /**
     * @param string $dir
     * @param int $total
     * @return bool
     */
    private function allChunksUploaded(string $dir, int $total): bool
    {
        return count(glob($dir . '/chunk_*')) == $total;
    }
    /**
     * @param string $filePath
     * @return array $origonalUrl
     * @throws Exception
     */
    public function getWasabiFile(string $filePath)
    {
        try {
            // Generate pre-signed URL
            $command = $this->s3->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $filePath,
            ]);
            $request = $this->s3->createPresignedRequest($command, '+2 minutes');
            return [
                "wasabi_url" => (string) $request->getUri()
            ];
        } catch (AwsException $e) {
            throw new Exception($e->getAwsErrorMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @param string $videoId
     * @return string $fileContent
     * @throws Exception
     */
    public function getMasterFileContent(string $videoId)
    {
        try {
            // 1️⃣ Create presigned URL (internal use only)
            $command = $this->s3->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $videoId,
            ]);

            $request = $this->s3->createPresignedRequest($command, '+5 minutes');
            $wasabiUrl = (string) $request->getUri();

            // 2️⃣ Fetch file content
            $playlist = Http::timeout(5)->get($wasabiUrl)->body();

            if (!$playlist) {
                throw new \Exception("Empty playlist");
            }
            // 3️⃣ Rewrite URLs to CDN paths
            $playlist = $this->rewritePlaylistUrls($playlist, $videoId);
            // 4️⃣ Return raw playlist content
            return $playlist;
        } catch (AwsException $e) {
            throw new \Exception($e->getAwsErrorMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * @param string $playlist
     * @param string $videoId
     * @return string 
     */
    private function rewritePlaylistUrls(string $playlist, string $videoId): string
    {
        $lines = explode("\n", $playlist);

        foreach ($lines as &$line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_starts_with($line, 'http')) {
                $line = "https://cdn.webplatf.site/" . dirname($videoId) . "/" . ltrim($line, '/');
            }
        }

        return implode("\n", $lines);
    }
    /**
     * @param string $path
     * @return string $cdnUrl
     * @throws Exception
     */
    public function getVideoUrl(string $path,)
    {
        try {
            $video = VideoUpload::where('video_id', $path)->first();
            if ($video) {
                $url = $video->media_url;
                $url = $this->signCdnUrl(path: $video->media_url, ttl: 300);
                return ["cdn_url" => $url];
            } else {
                // handle not found
                throw new Exception("Invalid Video Id");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @param array $imagesList
     * @throws Exception
     */
    public function imageList(): array
    {
        try {
            $images = ImageUpload::whereNotNull('title')->paginate(15);
            $imageList =  ImagesResources::collection($images->items())->resolve();
            return $imageList;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @param array $videosList
     * @throws Exception
     */
    public function videoList(): array
    {
        try {
            $videos = VideoUpload::with('thumbnail')->paginate(15);
            $videoList =  VideosResources::collection($videos->items())->resolve();
            return $videoList;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\VideoUpload;
use Aws\S3\S3Client;
use Exception;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\File;


class FileUploadJob extends Job implements ShouldQueue
{
    public string $fileName;
    public string $chunckDir;
    public int $thumbnailId;
    public int $tries = 3;  // try 3 times before failing
    public int $backoff = 5; // wait 10 seconds before retrying

    public function __construct(string $fileName, string $chunckDir, int $thumbnailId)
    {
        // ONLY primitive data
        $this->fileName = $fileName;
        $this->chunckDir = $chunckDir;
        $this->thumbnailId = $thumbnailId;
    }

    public function handle()
    {
        Log::info('ZIP Job started', ['file' => $this->fileName]);

        try {
            $zipPath = storage_path("app/zips/{$this->fileName}");

            if (!file_exists($zipPath)) {
                throw new Exception("ZIP file not found: {$zipPath}");
            }

            if (!class_exists(ZipArchive::class)) {
                throw new Exception("ZipArchive extension not enabled");
            }

            $extractPath = storage_path(
                'app/extracted/' . pathinfo($this->fileName, PATHINFO_FILENAME)
            );

            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            $zip = new ZipArchive();

            $status = $zip->open($zipPath);
            if ($status !== true) {
                throw new Exception("Failed to open ZIP file, status: {$status}");
            }

            $zip->extractTo($extractPath);
            $zip->close();

            Log::info('ZIP extracted', ['path' => $extractPath]);

            // Create S3 client INSIDE handle
            $s3 = new S3Client([
                'version' => 'latest',
                'region' => config('AppConfig.wasabi_region'),
                'endpoint' => config('AppConfig.wasabi_endpoint'),
                'credentials' => [
                    'key' => config('AppConfig.wasabi_key'),
                    'secret' => config('AppConfig.wasabi_secret'),
                ],
                'use_path_style_endpoint' => true,
                'http' => ['verify' => false],
            ]);


            $bucket = config('AppConfig.wasabi_bucket');

            $folder = 'uploads/' . pathinfo($this->fileName, PATHINFO_FILENAME);

            foreach (glob($extractPath . '/*') as $file) {
                if (is_file($file)) {
                    $s3->putObject([
                        'Bucket' => $bucket,
                        'Key' => $folder . '/' . basename($file),
                        'Body' => fopen($file, 'rb'),
                        'ContentType' => mime_content_type($file),
                    ]);
                }
            }
            $variantFile = glob($extractPath . '/v*.m3u8')[0] ?? null;
            $duration = 0;
            if ($variantFile && file_exists($variantFile)) {

                $duration = $this->getHlsDuration($variantFile);
            }
            VideoUpload::firstOrCreate(
                ['video_id' => pathinfo($this->fileName, PATHINFO_FILENAME)],
                [
                    'media_url'    => $folder . '/' . 'master.m3u8',
                    'thumbnail_id' => $this->thumbnailId,  // now included
                    'durations' => $duration ?? 0
                ]
            );
            // Cleanup
            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }

            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }

            if (File::exists($this->chunckDir)) {
                File::deleteDirectory($this->chunckDir);
            }
            Log::info('ZIP Job finished', ['file' => $this->fileName]);
        } catch (Exception $e) {
            Log::error('ZIP Job failed', [
                'file' => $this->fileName,
                'error' => $e->getMessage()
            ]);

            throw $e; // important so job is marked failed
        }
    }
    function getHlsDuration($playlist)
    {
        $duration = 0;

        $lines = file($playlist);

        foreach ($lines as $line) {

            if (strpos($line, '#EXTINF:') !== false) {

                preg_match('/#EXTINF:([0-9\.]+)/', $line, $match);

                if (isset($match[1])) {
                    $duration += (float)$match[1];
                }
            }
        }

        return (int) round($duration);
    }
}

<?php

namespace App\Jobs;

use Exception;
use App\Models\VideoUpload;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;



class FileUploadV1Job extends Job implements ShouldQueue
{
    public string $fileName;
    public string $finalPath;
    public int $thumbnailId;
    public int $duration;
    public int $tries = 3;  // try 3 times before failing
    public int $backoff = 5; // wait 10 seconds before retrying

    public function __construct(string $fileName, string $finalPath, int $thumbnailId, int $duration)
    {
        // ONLY primitive data
        $this->fileName = $fileName;
        $this->finalPath = $finalPath;
        $this->thumbnailId = $thumbnailId;
        $this->duration = $duration;
    }

    public function handle()
    {
        try {
            $url = config('AppConfig.kinescope_upload_url');
            $token = config('AppConfig.kinescope_api_key');
            $parentId = config('AppConfig.kinescope_project_id');
            $client = new Client();
            $response = $client->post(
                $url,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type'  => 'application/octet-stream',
                        'X-File-Name'   => basename($this->finalPath),
                        'X-Video-Title' => $this->fileName,
                        'X-Parent-ID' => $parentId
                    ],
                    'body' => fopen($this->finalPath, 'rb'),
                ]
            );
            if ($response->getStatusCode() == 200) {
                $body = json_decode($response->getBody()->getContents());
                $videoId = $body['data']['id'];
                VideoUpload::firstOrCreate(
                    ['video_id' => pathinfo($this->fileName, PATHINFO_FILENAME)],
                    [
                        'media_url'    => "",
                        'source_id' => $videoId,
                        'status' => 'Processing',
                        'thumbnail_id' => $this->thumbnailId,  // now included
                        'durations' => $this->duration ?? 0
                    ]
                );
            } else {
                throw new Exception(json_decode($response->getBody()->getContents()));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}

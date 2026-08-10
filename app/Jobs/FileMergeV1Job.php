<?php

namespace App\Jobs;

use App\Events\FileUploadV1Event;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\File;

class FileMergeV1Job extends Job implements ShouldQueue
{
    public string $fileName;
    public string $chunckDir;
    public string $finalPath;
    public int $thumbnailId;
    public int $duration;
    public string $ext;
    public int $tries = 3;  // try 3 times before failing
    public int $backoff = 5; // wait 10 seconds before retrying

    public function __construct(
        string $fileName,
        string $chunckDir,
        string $finalPath,
        string $ext,
        int $duration,
        int $thumbnailId
    ) {
        // ONLY primitive data
        $this->fileName = $fileName;
        $this->chunckDir = $chunckDir;
        $this->finalPath = $finalPath;
        $this->thumbnailId = $thumbnailId;
        $this->duration = $duration;
        $this->ext = $ext;
    }
    public function handle()
    {

        try {
            $ext = strtolower($this->ext);
            $tempDir = dirname($this->finalPath);
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0777, true, true);
            }
            $finalPath = $this->finalPath . "." . $ext;
            
            set_time_limit(0);
            $output = fopen($finalPath, 'wb');
            if (!$output) {
                throw new Exception("Unable to open output file");
            }
            // Get chunks
            $chunks = glob($this->chunckDir . '/chunk_*');
            
            if (!$chunks || count($chunks) === 0) {
                throw new Exception("No chunks found");
            }
            // Sort chunks
            natsort($chunks);
            // Merge chunks
            foreach ($chunks as $chunk) {

                $in = fopen($chunk, 'rb');

                if (!$in) {
                    throw new Exception("Unable to open chunk file: {$chunk}");
                }

                stream_copy_to_stream($in, $output);

                fclose($in);
            }

            fclose($output);
           
            // Detect mime
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $finalPath);
            finfo_close($finfo);
            $map = [
                'video/mp4'        => 'mp4'
            ];
            $detectedExt = $map[$mime] ?? null;
            
            // Validate extension
            if (!$detectedExt || $detectedExt !== $ext) {
                 
                if (file_exists($finalPath)) {
                    unlink($finalPath);
                }
                throw new Exception("File type mismatch");
            }
            // Cleanup chunks
            File::deleteDirectory($this->chunckDir);
            event(new FileUploadV1Event(
                fileName: $this->fileName,
                finalPath: $finalPath,
                thumbnailId: $this->thumbnailId,
                duration: $this->duration,
            ));
        } catch (Exception $e) {
            throw $e; // important so job is marked failed
        }
    }
}

<?php

namespace App\Jobs;

use App\Events\FileUploadEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\File;

class FileMergeJob extends Job implements ShouldQueue
{
    public string $fileName;
    public string $chunckDir;
    public string $finalPath;
    public int $thumbnailId;
    public int $tries = 3;  // try 3 times before failing
    public int $backoff = 5; // wait 10 seconds before retrying

    public function __construct(
        string $fileName,
        string $chunckDir,
        string $finalPath,
        int $thumbnailId
    ) {
        // ONLY primitive data
        $this->fileName = $fileName;
        $this->chunckDir = $chunckDir;
        $this->finalPath = $finalPath;
        $this->thumbnailId = $thumbnailId;
    }

    public function handle()
    {

        try {
            if (!File::exists(dirname($this->finalPath))) {
                File::makeDirectory(dirname($this->finalPath), 0755, true);
            }
            set_time_limit(0);
            $chunks = glob($this->chunckDir . '/chunk_*');
            natsort($chunks); // VERY important
            if (count($chunks) === 0) {
                throw new Exception('No chunks found');
            }
            $output = fopen($this->finalPath, 'wb');

            foreach ($chunks as $chunk) {
                $input = fopen($chunk, 'rb');
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
            fclose($output);
            event(new FileUploadEvent(
                fileName: $this->fileName,
                chunckDir: $this->chunckDir,
                thumbnailId: $this->thumbnailId
            ));
        } catch (Exception $e) {
            throw $e; // important so job is marked failed
        }
    }
}

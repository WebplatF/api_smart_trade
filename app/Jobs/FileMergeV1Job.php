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
    public string $ext;
    public int $tries = 3;  // try 3 times before failing
    public int $backoff = 5; // wait 10 seconds before retrying

    public function __construct(
        string $fileName,
        string $chunckDir,
        string $finalPath,
        string $ext,
        int $thumbnailId
    ) {
        // ONLY primitive data
        $this->fileName = $fileName;
        $this->chunckDir = $chunckDir;
        $this->finalPath = $finalPath;
        $this->thumbnailId = $thumbnailId;
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
            $duration = $this->getMp4Duration(filePath: $finalPath);
            event(new FileUploadV1Event(
                fileName: $this->fileName,
                finalPath: $finalPath,
                thumbnailId: $this->thumbnailId,
                duration: $duration,
            ));
        } catch (Exception $e) {
            throw $e; // important so job is marked failed
        }
    }
    function getMp4Duration(string $filePath)
    {
        $fp = fopen($filePath, 'rb');

        if (!$fp) {
            return 0;
        }

        while (!feof($fp)) {

            $sizeData = fread($fp, 4);
            $type = fread($fp, 4);

            if (strlen($sizeData) !== 4 || strlen($type) !== 4) {
                break;
            }

            $size = unpack('N', $sizeData)[1];

            // Handle extended size
            if ($size === 1) {
                $largeSize = fread($fp, 8);
                $parts = unpack('N2', $largeSize);

                $size = ($parts[1] * 4294967296) + $parts[2];
            }

            if ($type === 'moov') {

                $moovStart = ftell($fp);
                $moovEnd = $moovStart + $size - 8;

                while (ftell($fp) < $moovEnd) {

                    $atomSizeData = fread($fp, 4);
                    $atomType = fread($fp, 4);

                    if (strlen($atomSizeData) !== 4) {
                        break;
                    }

                    $atomSize = unpack('N', $atomSizeData)[1];

                    if ($atomType === 'mvhd') {

                        $version = ord(fread($fp, 1));

                        // flags
                        fread($fp, 3);

                        if ($version === 1) {

                            // creation + modification
                            fread($fp, 16);

                            $timescale = unpack('N', fread($fp, 4))[1];

                            $durationParts = unpack('N2', fread($fp, 8));

                            $duration =
                                ($durationParts[1] * 4294967296)
                                + $durationParts[2];
                        } else {

                            // creation + modification
                            fread($fp, 8);

                            $timescale = unpack('N', fread($fp, 4))[1];

                            $duration = unpack('N', fread($fp, 4))[1];
                        }

                        fclose($fp);

                        if ($timescale > 0) {
                            return (int) round($duration / $timescale);
                        }

                        return 0;
                    }

                    // Move to next atom
                    if ($atomSize > 8) {
                        fseek($fp, $atomSize - 8, SEEK_CUR);
                    }
                }

                break;
            }

            // Move to next atom
            if ($size > 8) {
                fseek($fp, $size - 8, SEEK_CUR);
            }
        }

        fclose($fp);

        return 0;
    }
}

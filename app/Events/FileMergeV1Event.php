<?php

namespace App\Events;

class FileMergeV1Event extends Event
{
    public $fileName;
    public $chunckDir;
    public $finalPath;
    public $thumbnailId;
    public $ext;
    public $duration;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        string $fileName,
        string $chunckDir,
        string $finalPath,
        int $thumbnailId,
        int $duration,
        string $ext
    ) {
        $this->fileName = $fileName;
        $this->chunckDir = $chunckDir;
        $this->finalPath = $finalPath;
        $this->thumbnailId = $thumbnailId;
        $this->ext = $ext;
        $this->duration = $duration;
    }
}

<?php

namespace App\Events;

class FileUploadV1Event extends Event
{
    public $fileName;
    public $finalPath;
    public $thumbnailId;
    public $duration;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        string $fileName,
        string $finalPath,
        int $thumbnailId,
        string $duration,
    ) {
        $this->fileName = $fileName;
        $this->finalPath = $finalPath;
        $this->thumbnailId = $thumbnailId;
        $this->duration = $duration;
    }
}

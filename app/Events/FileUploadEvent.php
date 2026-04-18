<?php

namespace App\Events;

class FileUploadEvent extends Event
{
    public $fileName;
    public $chunckDir;
    public $thumbnailId;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        string $fileName,
        string $chunckDir,
        int $thumbnailId
    ) {
        $this->fileName = $fileName;
        $this->chunckDir = $chunckDir;
        $this->thumbnailId = $thumbnailId;
    }
}

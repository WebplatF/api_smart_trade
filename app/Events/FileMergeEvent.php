<?php

namespace App\Events;

class FileMergeEvent extends Event
{
    public $fileName;
    public $chunckDir;
    public $finalPath;
    public $thumbnailId;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        string $fileName,
        string $chunckDir,
        string $finalPath,
        int $thumbnailId
    ) {
        $this->fileName = $fileName;
        $this->chunckDir = $chunckDir;
        $this->finalPath = $finalPath;
        $this->thumbnailId = $thumbnailId;
    }
}

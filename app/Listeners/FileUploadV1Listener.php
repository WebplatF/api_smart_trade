<?php

namespace App\Listeners;


use App\Events\FileUploadV1Event;
use App\Jobs\FileUploadV1Job;

class FileUploadV1Listener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\FileUploadV1Event  $event
     * @return void
     */
    public function handle(FileUploadV1Event $event)
    {
        dispatch(new FileUploadV1Job(
            fileName: $event->fileName,
            finalPath: $event->finalPath,
            thumbnailId: $event->thumbnailId,
            duration: $event->duration
        ));
    }
}

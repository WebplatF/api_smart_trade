<?php

namespace App\Listeners;

use App\Events\FileMergeV1Event;
use App\Jobs\FileMergeJob;
use App\Jobs\FileMergeV1Job;
use App\Jobs\FileUploadJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class FileMergeV1Listener
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
     * @param  \App\Events\FileMergeV1Event  $event
     * @return void
     */
    public function handle(FileMergeV1Event $event)
    {
        dispatch(new FileMergeV1Job(
            fileName: $event->fileName,
            chunckDir: $event->chunckDir,
            finalPath: $event->finalPath,
            thumbnailId: $event->thumbnailId,
            ext: $event->ext,
            duration:$event->duration
        ));
    }
}

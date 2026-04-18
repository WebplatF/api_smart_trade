<?php

namespace App\Listeners;

use App\Events\FileMergeEvent;
use App\Jobs\FileMergeJob;
use App\Jobs\FileUploadJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class FileMergeListener
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
     * @param  \App\Events\FileMergeEvent  $event
     * @return void
     */
    public function handle(FileMergeEvent $event)
    {
        dispatch(new FileMergeJob(
            fileName: $event->fileName,
            chunckDir: $event->chunckDir,
            finalPath: $event->finalPath,
            thumbnailId: $event->thumbnailId,
        ));
    }
}

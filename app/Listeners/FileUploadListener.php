<?php

namespace App\Listeners;

use App\Events\FileUploadEvent;
use App\Jobs\FileUploadJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class FileUploadListener
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
     * @param  \App\Events\FileUploadEvent  $event
     * @return void
     */
    public function handle(FileUploadEvent $event)
    {
        Log::info('ZIP LISTENER HIT', [
            'file' => $event->fileName
        ]);
        dispatch(new FileUploadJob(
            fileName: $event->fileName,
            chunckDir: $event->chunckDir,
            thumbnailId: $event->thumbnailId
        ));
        // app('queue')->push(new FileUploadJob($event->fileName));
    }
}

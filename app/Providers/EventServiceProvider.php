<?php

namespace App\Providers;

use App\Events\FileMergeEvent;
use App\Events\FileUploadEvent;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        FileMergeEvent::class => [
            \App\Listeners\FileMergeListener::class
        ],
        FileUploadEvent::class => [
            \App\Listeners\FileUploadListener::class
        ]
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}

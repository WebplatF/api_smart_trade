<?php

return [
    'default' => env('QUEUE_CONNECTION', 'database'),

    'connections' => [
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],
    ],

    'failed' => [
        'driver' => 'database-uuids',
        'driver' => 'database',
        'database' => env('DB_CONNECTION'),
        'table' => 'failed_jobs',
    ],
];

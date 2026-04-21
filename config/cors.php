<?php

// return [
//     'paths' => ['*'],
//     'allowed_methods' => ['*'],
//     'allowed_origins' => ['*'],
//     'allowed_headers' => ['*'],
//     'max_age' => 0,
// ];
return [
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        'http://localhost:4200',
        'https://smarttradeind.com',
        'https://admin.smarttradeind.com'
    ],

    'allowed_headers' => [
        'Origin',
        'Content-Type',
        'Authorization',
        'apikey',
    ],

    'supports_credentials' => true,
];

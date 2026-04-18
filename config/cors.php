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
        'https://webplatf.site',
        'https://smart-trade-001.web.app'
    ],

    'allowed_headers' => [
        'Origin',
        'Content-Type',
        'Authorization',
        'apikey',
    ],

    'supports_credentials' => true,
];

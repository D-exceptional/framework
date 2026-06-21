<?php

return [
    'driver' => env('CACHE_DRIVER', 'redis'),
    'stores' => [
        'file' => [
            'path' => storage_path('cache')
        ],

        'redis' => [
            'connection' => 'cache'
        ],

        'apcu' => []
    ]
];
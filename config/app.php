<?php

declare(strict_types=1);

return [

    'name'      => env('APP_NAME', ''),

    'env'       => env('APP_ENV', 'development'),

    'debug'     => env('APP_DEBUG', true),

    'url'       => env('APP_URL', ''), // Change to '' if the site is hosted online root domain (e.g., https://example.com)

    'log_file'  => env('APP_LOG_FILE', 'application.log'),

    'display'   => [

        'use_views' => env('APP_USE_VIEWS', true),

        'view_path' => env('APP_VIEW_PATH', ''), // Change to /views or the actual folder name where view files are stored 

    ],
    
];
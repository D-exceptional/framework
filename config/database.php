<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'mysql' => [
        'host' => env('DB_HOST') ?? '127.0.0.1',
        'name' => env('DB_NAME') ?? '',
        'user' => env('DB_USER') ?? '',
        'pass' => env('DB_PASS') ?? '',
    ]
];
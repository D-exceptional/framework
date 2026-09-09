<?php

declare(strict_types=1);

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'mysql' => [

        'host' => env('DB_HOST') ?? 'mysql',

        'name' => env('DB_DATABASE') ?? 'shopcity',

        'user' => env('DB_USERNAME') ?? 'shopcity_user',

        'pass' => env('DB_PASSWORD') ?? '',

    ],
];
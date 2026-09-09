<?php

declare(strict_types=1);

return [

    'driver'   => env('SESSION_DRIVER', 'redis'),

    'lifetime' => env('SESSION_LIFETIME', 7200),
    
    'cookie'   => env('SESSION_COOKIE', 'app_session'),
    
];
<?php

declare(strict_types=1);

return [

    'host'      => env('MAIL_HOST', 'smtp.example.com'),

    'username'  => env('MAIL_USERNAME', ''),

    'password'  => env('MAIL_PASSWORD', ''),

    'fromEmail' => env('MAIL_ADDRESS', ''),

    'fromName'  => env('MAIL_SENDER', ''),

    'port'      => env('MAIL_PORT', 587),

    'secure'    => env('MAIL_SECURE', 'tls'),

];
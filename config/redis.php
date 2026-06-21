<?php

$options = [
    'scheme'   => env('REDIS_SCHEME') ?? 'tcp',
    'host'     => env('REDIS_HOST') ?? '127.0.0.1',
    'port'     => env('REDIS_PORT') ?? 6379,
    'username' => env('REDIS_USERNAME') ?? 'default',
    'password' => env('REDIS_PASSWORD') ?? null,
    /*'database' => [
        'cache'       => 0,
        'session'     => 1,
        'queue'       => 2,
        'rateLimiter' => 3,
    ],*/
    'timeout'  => 5.0,
];

if (env('REDIS_SCHEME') === 'tls') {
    $options['ssl'] = [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ];
}

return $options;

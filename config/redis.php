<?php

declare(strict_types=1);

$options = [

    'scheme'   => env('REDIS_SCHEME') ?? 'tcp',

    'host'     => env('REDIS_HOST') ?? 'redis',

    'port'     => env('REDIS_PORT') ?? 6379,

    'username' => env('REDIS_USERNAME') ?? 'default',

    'password' => env('REDIS_PASSWORD') ?? null,

    'timeout'  => 5.0,

];

if (env('REDIS_SCHEME') === 'tls') {
    
    $options['ssl'] = [

        'verify_peer'      => false,

        'verify_peer_name' => false,
        
    ];
}

return $options;

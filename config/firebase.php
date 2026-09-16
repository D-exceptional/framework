<?php

declare(strict_types=1);

return [

    'type'           => env('FIREBASE_ACCOUNT_TYPE', ''),

    'project_id'     => env('FIREBASE_PROJECT_ID', ''),

    'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID', ''),

    'private_key'    => env('FIREBASE_PRIVATE_KEY', ''),

    'client_email'   => env('FIREBASE_CLIENT_EMAIL', ''),

    'client_id'      => env('FIREBASE_CLIENT_ID', ''),

    'auth_uri'       => env('FIREBASE_AUTH_URL', ''),

    'token_uri'      => env('FIREBASE_TOKEN_URL', ''),

    'auth_provider_x509_cert_url' => env('FIREBASE_AUTH_PROVIDER_CERT_URL', ''),

    'client_x509_cert_url'  => env('FIREBASE_CLIENT_CERT_URL', ''),

    'universe_domain' => env('FIREBASE_UNIVERSE_DOMAIN', ''),
    
];

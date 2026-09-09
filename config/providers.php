<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Application Service Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [

        App\Providers\CoreServiceProvider::class,

        App\Providers\ExceptionServiceProvider::class,

        App\Providers\RoutingServiceProvider::class,

        App\Providers\CacheServiceProvider::class,

        App\Providers\SessionServiceProvider::class,

        App\Providers\EventServiceProvider::class,

        App\Providers\ViewServiceProvider::class,

        // Add your custom service providers here. 
        // For example, if you have a service provider called 'MyCustomServiceProvider', you would add it like this:
        // App\Providers\MyCustomServiceProvider::class,

    ],

];
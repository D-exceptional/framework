<?php

declare(strict_types=1);

namespace App\Providers;

use App\Routing\Router;
use App\Routing\RouteLoader;
use App\Routing\UrlGenerator;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register routing services.
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Router
        |--------------------------------------------------------------------------
        */

        $this->container()->singleton(
            Router::class
        );

        /*
        |--------------------------------------------------------------------------
        | Route Loader
        |--------------------------------------------------------------------------
        */

        $this->container()->singleton(
            RouteLoader::class
        );

        /*
        |--------------------------------------------------------------------------
        | Url Generator
        |--------------------------------------------------------------------------
        */

        $this->container()->singleton(
            UrlGenerator::class,
            fn ($container) =>
                new UrlGenerator(
                    $container->get(Router::class)
                )
        );
    }

    /**
     * Boot the routing system.
     */
    public function boot(): void
    {
        $this->container()
            ->get(RouteLoader::class)
            ->load();
    }
}

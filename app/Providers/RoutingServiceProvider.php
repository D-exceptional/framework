<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;
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

        container()
            ->singleton(Router::class);

        /*
        |--------------------------------------------------------------------------
        | Route Loader
        |--------------------------------------------------------------------------
        */

        container()
            ->singleton(RouteLoader::class);

        /*
        |--------------------------------------------------------------------------
        | Url Generator
        |--------------------------------------------------------------------------
        */

        container()
            ->singleton(
                UrlGenerator::class,
                fn (Container $container) =>
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
        container()
            ->get(RouteLoader::class)
            ->load();
    }
}

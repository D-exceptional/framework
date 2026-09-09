<?php

declare(strict_types=1);

namespace App\Providers;

//use App\Core\Container;
use App\Core\Kernel;
use App\Core\View;
use App\Http\Request;
use App\Http\Response;
use App\Http\ResponseEmitter;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register core HTTP services.
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Request
        |--------------------------------------------------------------------------
        */

        $this->container()->singleton(
            Request::class
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        $this->container()->bind(
            Response::class,
            fn ($container) => new Response(
                $container->make(View::class)
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Response Emitter
        |--------------------------------------------------------------------------
        */

        $this->container()->singleton(
            ResponseEmitter::class
        );

        /*
        |--------------------------------------------------------------------------
        | HTTP Kernel
        |--------------------------------------------------------------------------
        */

        $this->container()->singleton(
            Kernel::class
        );
    }
}


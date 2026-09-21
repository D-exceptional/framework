<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;
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

        container()
            ->singleton(
                Request::class
            );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        container()
            ->bind(
                Response::class,
                fn (Container $container) => new Response(
                    $container->get(View::class)
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Response Emitter
        |--------------------------------------------------------------------------
        */

        container()
            ->singleton(
                ResponseEmitter::class
            );

        /*
        |--------------------------------------------------------------------------
        | HTTP Kernel
        |--------------------------------------------------------------------------
        */

        container()
            ->singleton(
                Kernel::class
            );
    }
}


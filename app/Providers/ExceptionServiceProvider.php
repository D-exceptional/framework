<?php

declare(strict_types=1);

namespace App\Providers;

//use App\Core\Container;
use App\Exceptions\ExceptionHandler;
use App\Http\Request;
use App\Http\Response;
use App\Http\ResponseEmitter;

class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Register the exception handler.
     */
    public function register(): void
    {
        $this->container()->singleton(
            ExceptionHandler::class,
            function ($container) {

                return new ExceptionHandler(
                    $container->get(Request::class),
                    $container->get(Response::class),
                    $container->get(ResponseEmitter::class),
                    ROOT_PATH . '/storage/logs/application.log'
                );
            }
        );
    }

    /**
     * Boot the exception handler.
     */
    public function boot(): void
    {
        $this->container()->get(
            ExceptionHandler::class
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;
use App\Contracts\SessionInterface;
use App\Session\Drivers\FileDriver;
use App\Session\Drivers\RedisDriver;
use App\Session\SessionManager;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the session service.
     */
    public function register(): void
    {
        container()
            ->singleton(
                SessionInterface::class,
                function (Container $container) {
                    $driver = config('session.driver', 'file');

                    return match ($driver) {

                        'file' => $container->get(FileDriver::class),

                        'redis' => $container->get(RedisDriver::class),

                        default => throw new \RuntimeException(
                            "Unsupported session driver [{$driver}]."
                        ),
                    };
                }
            );
    }

    /**
     * Boot the session service.
     */
    public function boot(): void
    {
        container()
            ->get(SessionInterface::class)
            ->start();
    }
}

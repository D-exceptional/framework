<?php

declare(strict_types=1);

namespace App\Providers;

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
        $this->container()->singleton(
            SessionInterface::class,
            function ($container) {
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

        /*
        $this->container()->singleton(
            SessionManager::class,
            SessionManager::class
        );
        */
    }

    /**
     * Boot the session service.
     */
    public function boot(): void
    {
        $this->container()
            ->get(SessionInterface::class)
            ->start();
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;
use App\Contracts\CacheInterface;
use App\Cache\Drivers\ApcuDriver;
use App\Cache\Drivers\FileDriver;
use App\Cache\Drivers\RedisDriver;
use App\Cache\CacheManager;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register the cache service.
     */
    public function register(): void
    {
        container()
            ->singleton(
                CacheInterface::class,
                function (Container $container) {

                    $driver = config('cache.driver', 'file');

                    return match ($driver) {

                        'apcu' => $container->get(ApcuDriver::class),

                        'file' => $container->get(FileDriver::class),

                        'redis' => $container->get(RedisDriver::class),

                        default => throw new \RuntimeException(
                            "Unsupported cache driver [{$driver}]."
                        ),
                    };
                }
            );
    }

    /**
     * Boot the cache service.
     */
    public function boot(): void
    {
        // No cache boot process is currently required.
    }
}

<?php

namespace App\Core;

use App\Redis\RedisManager;
use Dotenv\Dotenv;
use App\Contracts\CacheInterface;
use App\Contracts\SessionInterface;
use App\Cache\FileCache;
use App\Cache\ApcuCache;
use App\Cache\RedisCache;
use App\Session\Drivers\FileSessionDriver;
use App\Session\Drivers\RedisSessionDriver;

class Application
{
    protected Container $container;
    protected Config $config;

    public function __construct()
    {
        $this->container = new Container();

        // Register container
        $this->container->instance(Container::class, $this->container);

        // Register application itself
        $this->container->instance(Application::class, $this);
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function config(): Config
    {
        return $this->config;
    }

    // =========================================
    // ENVIRONMENT
    // =========================================
    public function loadEnvironment(): void
    {
        $envPath = ROOT_PATH . '/.env';

        if (file_exists($envPath)) {

            $dotenv = Dotenv::createImmutable(ROOT_PATH);
            $dotenv->load();
        }
    }

    // =========================================
    // CONFIGURATION
    // =========================================
    public function loadConfiguration(): void
    {
        $this->config = new Config();

        $this->config->load(ROOT_PATH . '/config');

        $this->container->instance(
            Config::class,
            $this->config
        );
    }

    // =========================================
    // BINDINGS
    // =========================================
    public function registerBindings(): void
    {
        // Cache binding
        $this->container->singleton(
            CacheInterface::class,
            function (Container $container) {

                return match (config('cache.driver', 'file')) {

                    'file'  => $container->get(FileCache::class),
                    'apcu'  => $container->get(ApcuCache::class),
                    'redis' => $container->get(RedisCache::class),

                    default => $container->get(FileCache::class)
                };
            }
        );

        // Session binding
        $this->container->singleton(
            SessionInterface::class,
            function (Container $container) {

                return match (config('session.driver', 'file')) {

                    'redis' => $container->get(RedisSessionDriver::class),
                    'file'  => $container->get(FileSessionDriver::class),

                    default => $container->get(FileSessionDriver::class)
                };
            }
        );
    }

    // =========================================
    // BOOT SERVICES
    // =========================================
    public function bootServices(): void
    {
        $session = $this->container->get(SessionInterface::class);
        $session->start();
    }
}
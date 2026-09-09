<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;

class Application
{
    /**
     * Service container.
     */
    protected Container $container;

    /**
     * Configuration repository.
     */
    protected Config $config;

    /**
     * Registered service providers.
     */
    protected array $providers = [];

    /**
     * Create the application.
     */
    public function __construct()
    {
        $this->container = new Container();

        /*
        |--------------------------------------------------------------------------
        | Register the application itself
        |--------------------------------------------------------------------------
        */

        $this->container->instance(
            Container::class,
            $this->container
        );

        $this->container->instance(
            self::class,
            $this
        );
    }

    /**
     * Get the container.
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Get the configuration.
     */
    public function config(): Config
    {
        return $this->config;
    }

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    */

    protected function loadEnvironment(): void
    {
        $env = ROOT_PATH . '/.env';

        if (!file_exists($env)) {
            // You can throw an exception here instead because the .env is required for the app to run
            return;
        }

        Dotenv::createImmutable(ROOT_PATH)->load();
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    protected function loadConfiguration(): void
    {
        $this->config = new Config();

        $this->config->load(ROOT_PATH . '/config');

        $this->container->instance(
            Config::class,
            $this->config
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    */

    /**
     * Register a service provider.
     */
    public function registerProvider(
        string $provider
    ): void {

        $instance = $this->container->make(
            $provider
        );

        $instance->register();

        $this->providers[] = $instance;
    }

    /**
     * Register multiple service providers.
     */
    public function registerProviders(
        array $providers
    ): void {

        foreach ($providers as $provider) {

            $this->registerProvider($provider);
        }
    }

    /**
     * Boot all registered service providers.
     */
    public function bootProviders(): void
    {
        foreach ($this->providers as $provider) {

            $provider->boot();
        }
    }


    /**
     * Boot the application.
     */
    public function boot(): void
    {
        $this->loadEnvironment();

        $this->loadConfiguration();

        $providers = $this->config->get(
            'providers.providers',
            []
        );

        $this->registerProviders($providers);

        $this->bootProviders();
    }
}
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Application;
use App\Core\Container;

abstract class ServiceProvider
{
    public function __construct(
        protected Application $app
    ) {}

    /**
     * Register services into the container.
     */
    public function register(): void
    {}

    /**
     * Boot services after all providers
     * have been registered.
     */
    public function boot(): void
    {}

    /**
     * Get the application container.
     */
    protected function container(): Container
    {
        return $this->app->container();
    }
}
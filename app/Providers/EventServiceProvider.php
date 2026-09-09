<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;
use App\Events\EventDispatcher;

class EventServiceProvider
{
    public function __construct(
        protected Container $container
    ) {}

    public function register(): void
    {
        $this->container->singleton(
            EventDispatcher::class,
            fn () => new EventDispatcher(
                $this->container
            )
        );
    }

    public function boot(): void
    {
        $dispatcher = $this->container->get(
            EventDispatcher::class
        );

        $events = config(
            'events',
            []
        );

        foreach ($events as $event => $listeners) {

            foreach ($listeners as $listener) {

                $dispatcher->listen(
                    $event,
                    $listener
                );
            }
        }
    }
}
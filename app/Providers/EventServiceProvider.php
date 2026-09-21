<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;
use App\Events\EventDispatcher;

class EventServiceProvider
{
    public function register(): void
    {
        container()
            ->singleton(
                EventDispatcher::class,
                fn (Container $container) => new EventDispatcher(
                    $container
                )
            );
    }

    public function boot(): void
    {
        $dispatcher = container()
            ->get(EventDispatcher::class);

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
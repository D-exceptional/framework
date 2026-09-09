<?php

declare(strict_types=1);

namespace App\Events;

use App\Core\Container;
use App\Contracts\EventInterface;

class EventDispatcher
{
    /**
     * Registered event listeners.
     */
    protected array $listeners = [];

    public function __construct(
        protected Container $container
    ) {}

    /**
     * Register a listener for an event.
     */
    public function listen(
        string $event,
        string $listener
    ): void {

        $this->listeners[$event][] = $listener;
    }

    /**
     * Dispatch an event.
     */
    public function dispatch(
        EventInterface $event
    ): void {

        $eventClass = $event::class;

        foreach (
            $this->listeners[$eventClass] ?? []
            as $listenerClass
        ) {

            $listener = $this->container->get(
                $listenerClass
            );

            $this->container->call(
                [$listener, 'handle'],
                [
                    'event' => $event
                ]
            );
        }
    }
}
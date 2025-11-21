<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Infrastructure\Event\Listener\EventListenerInterface;
use Psr\Log\LoggerInterface;

class ConsumerEventRouter
{
    /** @var EventListenerInterface */
    private array $listeners = [];

    public function __construct(
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function addListener(EventListenerInterface $listener): void
    {
        $event = $listener::subscribedTo();
        $this->listeners[$event][] = $listener;

        $this->logger?->debug('Registered listener for event', [
            'event' => $event,
            'listener' => get_class($listener),
        ]);
    }

    public function dispatch(string $event, array $payload): void
    {
        $listeners = $this->listeners[$event] ?? [];

        if (empty($listeners)) {
            $this->logger?->warning('No listeners registered for event', [
                'event' => $event,
            ]);
            return;
        }

        foreach ($listeners as $listener) {
            $this->logger?->info('Dispatching event to listener', [
                'event' => $event,
                'listener' => get_class($listener),
            ]);

            $listener->handle($payload);
        }
    }

    /**
     * Get all registered events
     *
     * @return string[]
     */
    public function getRegisteredEvents(): array
    {
        return array_keys($this->listeners);
    }

    /**
     * Check if an event has any listeners
     */
    public function hasListeners(string $event): bool
    {
        return !empty($this->listeners[$event]);
    }
}

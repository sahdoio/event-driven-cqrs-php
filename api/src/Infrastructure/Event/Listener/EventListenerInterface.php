<?php

declare(strict_types=1);

namespace App\Infrastructure\Event\Listener;

interface EventListenerInterface
{
    /**
     * Handle the event payload
     */
    public function handle(array $payload): void;

    /**
     * Get the event name this listener subscribes to
     */
    public static function subscribedTo(): string;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use Enqueue\AmqpLib\AmqpConnectionFactory;
use League\Event\Listener;

class RabbitMQEventPublisher implements Listener
{
    private mixed $context;
    private mixed $producer;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password
    ) {
        $connectionFactory = new AmqpConnectionFactory([
            'host' => $this->host,
            'port' => $this->port,
            'user' => $this->user,
            'pass' => $this->password,
            'vhost' => '/',
        ]);

        $this->context = $connectionFactory->createContext();
        $this->producer = $this->context->createProducer();
    }

    public function __invoke(object $event): void
    {
        $eventName = method_exists($event, 'eventName') ? $event->eventName() : get_class($event);
        $payload = method_exists($event, 'toArray') ? $event->toArray() : ['event' => $eventName];

        $queue = $this->context->createQueue('orders_events');
        $this->context->declareQueue($queue);

        $message = $this->context->createMessage(json_encode([
            'event' => $eventName,
            'payload' => $payload,
            'timestamp' => time(),
        ]));

        $this->producer->send($queue, $message);
    }
}

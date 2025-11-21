<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use Enqueue\AmqpLib\AmqpConnectionFactory;
use Psr\Log\LoggerInterface;

class MessageConsumer
{
    private mixed $context;
    private mixed $consumer;

    public function __construct(
        private readonly ConsumerEventRouter $eventRouter,
        private readonly string $rabbitHost,
        private readonly int $rabbitPort,
        private readonly string $rabbitUser,
        private readonly string $rabbitPass,
        private readonly ?LoggerInterface $logger = null
    ) {
        $connectionFactory = new AmqpConnectionFactory([
            'host' => $this->rabbitHost,
            'port' => $this->rabbitPort,
            'user' => $this->rabbitUser,
            'pass' => $this->rabbitPass,
            'vhost' => '/',
        ]);

        $this->context = $connectionFactory->createContext();
        $queue = $this->context->createQueue('orders_events');
        $this->context->declareQueue($queue);
        $this->consumer = $this->context->createConsumer($queue);
    }

    public function consume(): void
    {
        $this->logger?->info('Starting message consumer...');
        echo "Starting consumer...\n";
        echo sprintf(
            "Listening for events: %s\n",
            implode(', ', $this->eventRouter->getRegisteredEvents())
        );

        while (true) {
            $message = $this->consumer->receive(1000);

            if ($message === null) {
                continue;
            }

            try {
                $data = json_decode($message->getBody(), true);
                $event = $data['event'];
                $payload = $data['payload'];

                $this->eventRouter->dispatch($event, $payload);
                $this->consumer->acknowledge($message);

                $this->logger?->info('Processed event', ['event' => $event]);
                echo sprintf("Processed event: %s\n", $event);
            } catch (\Exception $e) {
                $this->consumer->reject($message);
                $this->logger?->error('Error processing event', [
                    'error' => $e->getMessage(),
                ]);
                echo sprintf("Error processing event: %s\n", $e->getMessage());
            }
        }
    }
}

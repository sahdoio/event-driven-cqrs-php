<?php

declare(strict_types=1);

namespace App\Infrastructure\Consumer;

use App\Infrastructure\Database\MongoConnection;
use Enqueue\AmqpLib\AmqpConnectionFactory;
use MongoDB\Collection;

class OrderEventConsumer
{
    private Collection $collection;
    private $context;
    private $consumer;

    public function __construct(
        private readonly MongoConnection $mongoConnection,
        private readonly string $rabbitHost,
        private readonly int $rabbitPort,
        private readonly string $rabbitUser,
        private readonly string $rabbitPass
    ) {
        $this->collection = $this->mongoConnection->getDatabase()->selectCollection('orders');

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
        echo "Starting consumer...\n";

        while (true) {
            $message = $this->consumer->receive(1000);

            if ($message === null) {
                continue;
            }

            try {
                $data = json_decode($message->getBody(), true);
                $this->processEvent($data);
                $this->consumer->acknowledge($message);
                echo sprintf("Processed event: %s\n", $data['event']);
            } catch (\Exception $e) {
                $this->consumer->reject($message);
                echo sprintf("Error processing event: %s\n", $e->getMessage());
            }
        }
    }

    private function processEvent(array $data): void
    {
        $event = $data['event'];
        $payload = $data['payload'];

        match ($event) {
            'order.created' => $this->handleOrderCreated($payload),
            'order.updated' => $this->handleOrderUpdated($payload),
            default => throw new \RuntimeException('Unknown event: ' . $event),
        };
    }

    private function handleOrderCreated(array $payload): void
    {
        $this->collection->insertOne([
            '_id' => $payload['order_id'],
            'customer_name' => $payload['customer_name'],
            'customer_email' => $payload['customer_email'],
            'items' => $payload['items'],
            'total_amount' => $payload['total_amount'],
            'status' => $payload['status'],
            'created_at' => $payload['created_at'],
            'updated_at' => null,
        ]);
    }

    private function handleOrderUpdated(array $payload): void
    {
        $updateData = [];

        if (isset($payload['items'])) {
            $updateData['items'] = $payload['items'];
        }

        if (isset($payload['total_amount'])) {
            $updateData['total_amount'] = $payload['total_amount'];
        }

        if (isset($payload['status'])) {
            $updateData['status'] = $payload['status'];
        }

        if (isset($payload['updated_at'])) {
            $updateData['updated_at'] = $payload['updated_at'];
        }

        $this->collection->updateOne(
            ['_id' => $payload['order_id']],
            ['$set' => $updateData]
        );
    }
}

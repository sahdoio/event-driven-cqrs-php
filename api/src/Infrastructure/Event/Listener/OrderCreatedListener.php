<?php

declare(strict_types=1);

namespace App\Infrastructure\Event\Listener;

use App\Infrastructure\Database\MongoConnection;

class OrderCreatedListener implements EventListenerInterface
{
    public function __construct(
        private readonly MongoConnection $mongoConnection
    ) {
    }

    public static function subscribedTo(): string
    {
        return 'order.created';
    }

    public function handle(array $payload): void
    {
        $collection = $this->mongoConnection->getDatabase()->selectCollection('orders');

        $collection->insertOne([
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
}

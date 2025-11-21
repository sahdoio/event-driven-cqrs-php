<?php

declare(strict_types=1);

namespace App\Infrastructure\Event\Listener;

use App\Infrastructure\Event\Listener\EventListenerInterface;
use App\Infrastructure\Database\MongoConnection;

class OrderUpdatedListener implements EventListenerInterface
{
    public function __construct(
        private readonly MongoConnection $mongoConnection
    ) {
    }

    public static function subscribedTo(): string
    {
        return 'order.updated';
    }

    public function handle(array $payload): void
    {
        $collection = $this->mongoConnection->getDatabase()->selectCollection('orders');

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

        $collection->updateOne(
            ['_id' => $payload['order_id']],
            ['$set' => $updateData]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Query;

use App\Domain\Order\OrderNotFoundException;
use App\Infrastructure\Database\MongoConnection;
use MongoDB\Collection;

class MongoOrderQueryRepository
{
    private Collection $collection;

    public function __construct(MongoConnection $mongoConnection)
    {
        $this->collection = $mongoConnection->getDatabase()->selectCollection('orders');
    }

    public function findById(string $id): array
    {
        $document = $this->collection->findOne(['_id' => $id]);

        if ($document === null) {
            throw new OrderNotFoundException();
        }

        return $this->format($document);
    }

    public function findAll(): array
    {
        $cursor = $this->collection->find([], [
            'sort' => ['created_at' => -1]
        ]);

        $orders = [];
        foreach ($cursor as $document) {
            $orders[] = $this->format($document);
        }

        return $orders;
    }

    private function format(object $document): array
    {
        return [
            'id' => $document->_id,
            'customer_name' => $document->customer_name,
            'customer_email' => $document->customer_email,
            'items' => $document->items,
            'total_amount' => $document->total_amount,
            'status' => $document->status,
            'created_at' => $document->created_at,
            'updated_at' => $document->updated_at ?? null,
        ];
    }
}

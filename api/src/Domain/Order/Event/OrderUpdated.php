<?php

declare(strict_types=1);

namespace App\Domain\Order\Event;

use League\Event\HasEventName;

class OrderUpdated implements HasEventName
{
    public function __construct(
        public readonly string $orderId,
        public readonly ?array $items,
        public readonly ?float $totalAmount,
        public readonly ?string $status,
        public readonly string $updatedAt
    ) {
    }

    public function eventName(): string
    {
        return 'order.updated';
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->orderId,
            'items' => $this->items,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'updated_at' => $this->updatedAt,
        ], fn($value) => $value !== null);
    }
}

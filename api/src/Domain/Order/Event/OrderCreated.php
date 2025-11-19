<?php

declare(strict_types=1);

namespace App\Domain\Order\Event;

use League\Event\HasEventName;

class OrderCreated implements HasEventName
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly array $items,
        public readonly float $totalAmount,
        public readonly string $status,
        public readonly string $createdAt
    ) {
    }

    public function eventName(): string
    {
        return 'order.created';
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'items' => $this->items,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}

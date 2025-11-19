<?php

declare(strict_types=1);

namespace App\Application\Command\Order;

class CreateOrderCommand
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly array $items,
        public readonly float $totalAmount
    ) {
    }
}

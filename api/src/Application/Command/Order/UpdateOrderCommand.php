<?php

declare(strict_types=1);

namespace App\Application\Command\Order;

class UpdateOrderCommand
{
    public function __construct(
        public readonly string $id,
        public readonly ?array $items = null,
        public readonly ?float $totalAmount = null,
        public readonly ?string $status = null
    ) {
    }
}

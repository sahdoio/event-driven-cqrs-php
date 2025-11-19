<?php

declare(strict_types=1);

namespace App\Domain\Order;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class Order
{
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        private UuidInterface $id,
        private string $customerName,
        private string $customerEmail,
        private array $items,
        private float $totalAmount,
        private OrderStatus $status
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateStatus(OrderStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateItems(array $items, float $totalAmount): void
    {
        $this->items = $items;
        $this->totalAmount = $totalAmount;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'items' => $this->items,
            'total_amount' => $this->totalAmount,
            'status' => $this->status->value,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}

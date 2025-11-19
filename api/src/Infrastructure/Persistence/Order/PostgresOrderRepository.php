<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Order;

use App\Domain\Order\Order;
use App\Domain\Order\OrderNotFoundException;
use App\Domain\Order\OrderStatus;
use App\Infrastructure\Database\PostgresConnection;
use PDO;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PostgresOrderRepository
{
    public function __construct(
        private readonly PostgresConnection $connection
    ) {
    }

    public function save(Order $order): void
    {
        $pdo = $this->connection->getConnection();

        $sql = "INSERT INTO orders (id, customer_name, customer_email, items, total_amount, status, created_at, updated_at)
                VALUES (:id, :customer_name, :customer_email, :items, :total_amount, :status, :created_at, :updated_at)
                ON CONFLICT (id) DO UPDATE SET
                    customer_name = :customer_name,
                    customer_email = :customer_email,
                    items = :items,
                    total_amount = :total_amount,
                    status = :status,
                    updated_at = :updated_at";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $order->getId()->toString(),
            'customer_name' => $order->getCustomerName(),
            'customer_email' => $order->getCustomerEmail(),
            'items' => json_encode($order->getItems()),
            'total_amount' => $order->getTotalAmount(),
            'status' => $order->getStatus()->value,
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $order->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(UuidInterface $id): Order
    {
        $pdo = $this->connection->getConnection();

        $sql = "SELECT * FROM orders WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id->toString()]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new OrderNotFoundException();
        }

        return $this->hydrate($row);
    }

    private function hydrate(array $row): Order
    {
        $order = new Order(
            Uuid::fromString($row['id']),
            $row['customer_name'],
            $row['customer_email'],
            json_decode($row['items'], true),
            (float) $row['total_amount'],
            OrderStatus::from($row['status'])
        );

        return $order;
    }
}

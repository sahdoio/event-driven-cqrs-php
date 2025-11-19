<?php

declare(strict_types=1);

namespace App\Application\Command\Order;

use App\Domain\Order\Event\OrderUpdated;
use App\Domain\Order\OrderStatus;
use App\Infrastructure\Persistence\Order\PostgresOrderRepository;
use League\Event\EventDispatcher;
use Ramsey\Uuid\Uuid;

class UpdateOrderHandler
{
    public function __construct(
        private readonly PostgresOrderRepository $orderRepository,
        private readonly EventDispatcher $eventDispatcher
    ) {
    }

    public function handle(UpdateOrderCommand $command): void
    {
        $order = $this->orderRepository->findById(Uuid::fromString($command->id));

        if ($command->items !== null && $command->totalAmount !== null) {
            $order->updateItems($command->items, $command->totalAmount);
        }

        if ($command->status !== null) {
            $order->updateStatus(OrderStatus::from($command->status));
        }

        $this->orderRepository->save($order);

        // Dispatch event
        $event = new OrderUpdated(
            $command->id,
            $command->items,
            $command->totalAmount,
            $command->status,
            $order->getUpdatedAt()->format('Y-m-d H:i:s')
        );

        $this->eventDispatcher->dispatch($event);
    }
}

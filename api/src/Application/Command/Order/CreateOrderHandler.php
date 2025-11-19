<?php

declare(strict_types=1);

namespace App\Application\Command\Order;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Order;
use App\Domain\Order\OrderStatus;
use App\Infrastructure\Persistence\Order\PostgresOrderRepository;
use League\Event\EventDispatcher;
use Ramsey\Uuid\Uuid;

class CreateOrderHandler
{
    public function __construct(
        private readonly PostgresOrderRepository $orderRepository,
        private readonly EventDispatcher $eventDispatcher
    ) {
    }

    public function handle(CreateOrderCommand $command): string
    {
        $orderId = Uuid::uuid4();

        $order = new Order(
            $orderId,
            $command->customerName,
            $command->customerEmail,
            $command->items,
            $command->totalAmount,
            OrderStatus::PENDING
        );

        $this->orderRepository->save($order);

        // Dispatch event
        $event = new OrderCreated(
            $orderId->toString(),
            $order->getCustomerName(),
            $order->getCustomerEmail(),
            $order->getItems(),
            $order->getTotalAmount(),
            $order->getStatus()->value,
            $order->getCreatedAt()->format('Y-m-d H:i:s')
        );

        $this->eventDispatcher->dispatch($event);

        return $orderId->toString();
    }
}

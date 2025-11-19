<?php

declare(strict_types=1);

namespace App\Application\Actions\Order;

use App\Application\Actions\Action;
use App\Application\Command\Order\CreateOrderCommand;
use App\Application\Command\Order\CreateOrderHandler;
use Psr\Http\Message\ResponseInterface as Response;

class CreateOrderAction extends Action
{
    public function __construct(
        private readonly CreateOrderHandler $handler
    ) {
    }

    protected function action(): Response
    {
        $data = $this->getFormData();

        $command = new CreateOrderCommand(
            $data['customer_name'] ?? '',
            $data['customer_email'] ?? '',
            $data['items'] ?? [],
            (float) ($data['total_amount'] ?? 0.0)
        );

        $orderId = $this->handler->handle($command);

        return $this->respondWithData([
            'id' => $orderId,
            'message' => 'Order created successfully'
        ], 201);
    }
}

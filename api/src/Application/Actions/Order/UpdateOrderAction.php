<?php

declare(strict_types=1);

namespace App\Application\Actions\Order;

use App\Application\Actions\Action;
use App\Application\Command\Order\UpdateOrderCommand;
use App\Application\Command\Order\UpdateOrderHandler;
use Psr\Http\Message\ResponseInterface as Response;

class UpdateOrderAction extends Action
{
    public function __construct(
        private readonly UpdateOrderHandler $handler
    ) {
    }

    protected function action(): Response
    {
        $orderId = $this->resolveArg('id');
        $data = $this->getFormData();

        $command = new UpdateOrderCommand(
            $orderId,
            $data['items'] ?? null,
            isset($data['total_amount']) ? (float) $data['total_amount'] : null,
            $data['status'] ?? null
        );

        $this->handler->handle($command);

        return $this->respondWithData([
            'message' => 'Order updated successfully'
        ]);
    }
}

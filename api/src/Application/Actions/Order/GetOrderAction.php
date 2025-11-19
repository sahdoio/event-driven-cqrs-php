<?php

declare(strict_types=1);

namespace App\Application\Actions\Order;

use App\Application\Actions\Action;
use App\Application\Query\Order\GetOrderHandler;
use App\Application\Query\Order\GetOrderQuery;
use Psr\Http\Message\ResponseInterface as Response;

class GetOrderAction extends Action
{
    public function __construct(
        private readonly GetOrderHandler $handler
    ) {
    }

    protected function action(): Response
    {
        $orderId = $this->resolveArg('id');

        $query = new GetOrderQuery($orderId);
        $order = $this->handler->handle($query);

        return $this->respondWithData($order);
    }
}

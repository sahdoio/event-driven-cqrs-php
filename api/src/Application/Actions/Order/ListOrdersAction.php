<?php

declare(strict_types=1);

namespace App\Application\Actions\Order;

use App\Application\Actions\Action;
use App\Application\Query\Order\GetAllOrdersHandler;
use App\Application\Query\Order\GetAllOrdersQuery;
use Psr\Http\Message\ResponseInterface as Response;

class ListOrdersAction extends Action
{
    public function __construct(
        private readonly GetAllOrdersHandler $handler
    ) {
    }

    protected function action(): Response
    {
        $query = new GetAllOrdersQuery();
        $orders = $this->handler->handle($query);

        return $this->respondWithData($orders);
    }
}

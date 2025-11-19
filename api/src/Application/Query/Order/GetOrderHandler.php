<?php

declare(strict_types=1);

namespace App\Application\Query\Order;

use App\Infrastructure\Query\MongoOrderQueryRepository;

class GetOrderHandler
{
    public function __construct(
        private readonly MongoOrderQueryRepository $queryRepository
    ) {
    }

    public function handle(GetOrderQuery $query): array
    {
        return $this->queryRepository->findById($query->id);
    }
}

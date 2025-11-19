<?php

declare(strict_types=1);

namespace App\Application\Query\Order;

use App\Infrastructure\Query\MongoOrderQueryRepository;

class GetAllOrdersHandler
{
    public function __construct(
        private readonly MongoOrderQueryRepository $queryRepository
    ) {
    }

    public function handle(GetAllOrdersQuery $query): array
    {
        return $this->queryRepository->findAll();
    }
}

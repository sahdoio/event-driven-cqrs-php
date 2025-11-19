<?php

declare(strict_types=1);

use App\Application\Actions\Order\CreateOrderAction;
use App\Application\Actions\Order\GetOrderAction;
use App\Application\Actions\Order\ListOrdersAction;
use App\Application\Actions\Order\UpdateOrderAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('CQRS Event-Driven Order API');
        return $response;
    });

    // Order endpoints - CQRS
    $app->group('/orders', function (Group $group) {
        // Queries (Read operations from MongoDB)
        $group->get('', ListOrdersAction::class);
        $group->get('/{id}', GetOrderAction::class);

        // Commands (Write operations to PostgreSQL)
        $group->post('', CreateOrderAction::class);
        $group->patch('/{id}', UpdateOrderAction::class);
    });

    // Legacy User endpoints
    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};

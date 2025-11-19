<?php

declare(strict_types=1);

use App\Application\Command\Order\CreateOrderHandler;
use App\Application\Command\Order\UpdateOrderHandler;
use App\Application\Query\Order\GetAllOrdersHandler;
use App\Application\Query\Order\GetOrderHandler;
use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Consumer\OrderEventConsumer;
use App\Infrastructure\Database\MongoConnection;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Event\RabbitMQEventListener;
use App\Infrastructure\Persistence\Order\PostgresOrderRepository;
use App\Infrastructure\Query\MongoOrderQueryRepository;
use DI\ContainerBuilder;
use League\Event\EventDispatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        // Database connections
        PostgresConnection::class => function () {
            return new PostgresConnection(
                getenv('DB_WRITE_HOST') ?: 'localhost',
                (int) (getenv('DB_WRITE_PORT') ?: 5432),
                getenv('DB_WRITE_NAME') ?: 'orders_write',
                getenv('DB_WRITE_USER') ?: 'postgres',
                getenv('DB_WRITE_PASS') ?: 'postgres'
            );
        },

        MongoConnection::class => function () {
            return new MongoConnection(
                getenv('DB_READ_HOST') ?: 'localhost',
                (int) (getenv('DB_READ_PORT') ?: 27017),
                getenv('DB_READ_NAME') ?: 'orders_read',
                getenv('DB_READ_USER') ?: 'mongo',
                getenv('DB_READ_PASS') ?: 'mongo'
            );
        },

        // Event system
        RabbitMQEventListener::class => function () {
            return new RabbitMQEventListener(
                getenv('RABBITMQ_HOST') ?: 'localhost',
                (int) (getenv('RABBITMQ_PORT') ?: 5672),
                getenv('RABBITMQ_USER') ?: 'rabbitmq',
                getenv('RABBITMQ_PASS') ?: 'rabbitmq'
            );
        },

        EventDispatcher::class => function (ContainerInterface $c) {
            $dispatcher = new EventDispatcher();
            $dispatcher->subscribeTo('order.created', $c->get(RabbitMQEventListener::class));
            $dispatcher->subscribeTo('order.updated', $c->get(RabbitMQEventListener::class));
            return $dispatcher;
        },

        // Repositories
        PostgresOrderRepository::class => function (ContainerInterface $c) {
            return new PostgresOrderRepository($c->get(PostgresConnection::class));
        },

        MongoOrderQueryRepository::class => function (ContainerInterface $c) {
            return new MongoOrderQueryRepository($c->get(MongoConnection::class));
        },

        // Command handlers
        CreateOrderHandler::class => function (ContainerInterface $c) {
            return new CreateOrderHandler(
                $c->get(PostgresOrderRepository::class),
                $c->get(EventDispatcher::class)
            );
        },

        UpdateOrderHandler::class => function (ContainerInterface $c) {
            return new UpdateOrderHandler(
                $c->get(PostgresOrderRepository::class),
                $c->get(EventDispatcher::class)
            );
        },

        // Query handlers
        GetOrderHandler::class => function (ContainerInterface $c) {
            return new GetOrderHandler($c->get(MongoOrderQueryRepository::class));
        },

        GetAllOrdersHandler::class => function (ContainerInterface $c) {
            return new GetAllOrdersHandler($c->get(MongoOrderQueryRepository::class));
        },

        // Consumer
        OrderEventConsumer::class => function (ContainerInterface $c) {
            return new OrderEventConsumer(
                $c->get(MongoConnection::class),
                getenv('RABBITMQ_HOST') ?: 'localhost',
                (int) (getenv('RABBITMQ_PORT') ?: 5672),
                getenv('RABBITMQ_USER') ?: 'rabbitmq',
                getenv('RABBITMQ_PASS') ?: 'rabbitmq'
            );
        },
    ]);
};

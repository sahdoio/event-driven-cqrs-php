#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Infrastructure\Consumer\OrderEventConsumer;
use App\Infrastructure\Database\MongoConnection;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Build DI Container
$containerBuilder = new ContainerBuilder();

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

$container = $containerBuilder->build();

// Get the consumer from the container
$consumer = $container->get(OrderEventConsumer::class);

// Start consuming events
echo "Order Event Consumer started\n";
echo "Listening for events on RabbitMQ...\n";

$consumer->consume();

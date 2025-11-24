#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Infrastructure\Event\MessageConsumer;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Build DI Container
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

$container = $containerBuilder->build();

// Get the message consumer from the container
$consumer = $container->get(MessageConsumer::class);

// Start consuming events
echo "Message Consumer started\n";
echo "Listening for events on RabbitMQ...\n";

$consumer->consume();

<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use MongoDB\Client;
use MongoDB\Database;

class MongoConnection
{
    private ?Client $client = null;
    private ?Database $database = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password
    ) {
    }

    public function getDatabase(): Database
    {
        if ($this->database === null) {
            $this->connect();
        }

        return $this->database;
    }

    private function connect(): void
    {
        $uri = sprintf(
            'mongodb://%s:%s@%s:%d',
            $this->username,
            $this->password,
            $this->host,
            $this->port
        );

        try {
            $this->client = new Client($uri);
            $this->database = $this->client->selectDatabase($this->database);
        } catch (\Exception $e) {
            throw new \RuntimeException('MongoDB connection failed: ' . $e->getMessage());
        }
    }
}

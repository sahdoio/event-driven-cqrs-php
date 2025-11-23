# Event-Driven CQRS Order Management System

A PHP-based implementation of CQRS (Command Query Responsibility Segregation) with Event-Driven Architecture.

## Overview

This project implements **CQRS (Command Query Responsibility Segregation)** with an **Event-Driven Architecture**, using **Slim Framework**, **RabbitMQ**, and **Enqueue**. It uses **PostgreSQL for write operations** and **MongoDB for read operations**.

## Architecture

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│   Client    │─────>│  Slim API    │─────>│ PostgreSQL  │
└─────────────┘      │  (Commands)  │      │   (Write)   │
                     └──────────────┘      └─────────────┘
                            │
                            v
                     ┌──────────────┐
                     │  RabbitMQ    │
                     │   (Events)   │
                     └──────────────┘
                            │
                            v
                     ┌──────────────┐      ┌─────────────┐
                     │   Consumer   │─────>│   MongoDB   │
                     │  (Listener)  │      │    (Read)   │
                     └──────────────┘      └─────────────┘
                                                  ^
                     ┌──────────────┐            │
                     │  Slim API    │────────────┘
                     │   (Queries)  │
                     └──────────────┘
```

## Technologies Used

- **PHP 8.4** (Slim Framework)
- **PostgreSQL** (Write database)
- **MongoDB** (Read database)
- **RabbitMQ** (Message broker for event-driven communication)
- **Enqueue** (PSR-14 event dispatcher & message queue abstraction)
- **League Event** (Event dispatcher)
- **Docker** (Containerization for services)

## Project Structure

```
event-driven-cqrs-php/
├── api/
│   ├── app/
│   │   ├── dependencies.php      # DI Container configuration
│   │   ├── routes.php            # API route definitions
│   │   └── settings.php          # Application settings
│   ├── bin/
│   │   └── consumer.php          # Event consumer worker
│   ├── src/
│   │   ├── Application/
│   │   │   ├── Actions/
│   │   │   │   └── Order/        # HTTP Action handlers
│   │   │   ├── Command/
│   │   │   │   └── Order/        # Command objects & handlers
│   │   │   └── Query/
│   │   │       └── Order/        # Query objects & handlers
│   │   ├── Domain/
│   │   │   └── Order/
│   │   │       ├── Event/        # Domain events
│   │   │       ├── Order.php     # Domain entity
│   │   │       └── OrderStatus.php
│   │   └── Infrastructure/
│   │       ├── Consumer/         # RabbitMQ consumers
│   │       ├── Database/         # DB connections
│   │       ├── Event/            # Event listeners
│   │       ├── Persistence/      # PostgreSQL repositories
│   │       └── Query/            # MongoDB query repositories
│   ├── composer.json
│   └── public/
│       └── index.php
├── docker/
│   ├── php/
│   │   └── Dockerfile
│   └── postgres/
│       └── init.sql
└── docker-compose.yml
```

## API Endpoints

### Commands (Write Operations)

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/orders` | Create a new order |
| PATCH | `/orders/{id}` | Update an order |

### Queries (Read Operations)

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/orders` | Get all orders |
| GET | `/orders/{id}` | Get order by ID |

## Event Flow

1. **Command is executed** → Stored in PostgreSQL.
2. **Event is dispatched** → Sent to RabbitMQ.
3. **Consumers process the event** → Data is updated in MongoDB.
4. **Queries fetch data from MongoDB** → Optimized read model.

## Setup Instructions

### Prerequisites

- Docker & Docker Compose
- Git

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd event-driven-cqrs-php
```

2. Start the services:
```bash
make go
```

Or for a clean start (removes all data):
```bash
make go-hard
```

This will start:
- PostgreSQL (port 5432) - Write database
- MongoDB (port 27017) - Read database
- RabbitMQ (port 5672, management UI on 15672) - Message broker
- PHP API (port 8080) - REST API
- Consumer - Event processor

3. Verify services are running:
```bash
docker compose ps
```

### Service Credentials

| Service | Host | Port | Username | Password | Database | Auth Source |
|---------|------|------|----------|----------|----------|-------------|
| PostgreSQL | localhost | 5432 | `postgres` | `postgres` | `orders_write` | - |
| MongoDB | localhost | 27017 | `mongo` | `mongo` | `orders_read` | `orders_read` |
| RabbitMQ | localhost | 5672/15672 | `rabbitmq` | `rabbitmq` | - | - |

#### Connection Strings

```bash
# PostgreSQL
postgresql://postgres:postgres@localhost:5432/orders_write

# MongoDB
mongodb://mongo:mongo@localhost:27017/orders_read?authSource=orders_read

# RabbitMQ (AMQP)
amqp://rabbitmq:rabbitmq@localhost:5672
```

### Accessing Services

- **API**: http://localhost:8080
- **RabbitMQ Management**: http://localhost:15672 (user: `rabbitmq`, pass: `rabbitmq`)
- **PostgreSQL**: localhost:5432 (user: `postgres`, pass: `postgres`, db: `orders_write`)
- **MongoDB**: localhost:27017 (user: `mongo`, pass: `mongo`, db: `orders_read`)

## API Testing with Bruno

This project includes a [Bruno](https://www.usebruno.com/) API collection for testing all endpoints.

### Setup

1. Install Bruno from https://www.usebruno.com/
2. Open Bruno and click "Open Collection"
3. Navigate to `docs/bruno` folder in this project
4. Select the "Local" environment from the environment dropdown

### Available Requests

| Request | Method | Description |
|---------|--------|-------------|
| Health Check | GET | Check if API is running |
| Create Order | POST | Create a new order |
| Get All Orders | GET | Retrieve all orders from MongoDB |
| Get Order by ID | GET | Retrieve a specific order |
| Update Order | PATCH | Update order status |

### Environment Variables

The collection uses a `baseUrl` variable set to `http://localhost:8080`. You can modify this in the "Local" environment if needed.

## Usage Examples

### Create Order (Command)

```bash
curl -X POST http://localhost:8080/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "items": [
      {"product": "Laptop", "quantity": 1, "price": 999.99},
      {"product": "Mouse", "quantity": 2, "price": 25.00}
    ],
    "total_amount": 1049.99
  }'
```

Response:
```json
{
  "statusCode": 201,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "message": "Order created successfully"
  }
}
```

### Get All Orders (Query)

```bash
curl http://localhost:8080/orders
```

### Get Order by ID (Query)

```bash
curl http://localhost:8080/orders/550e8400-e29b-41d4-a716-446655440000
```

### Update Order (Command)

```bash
curl -X PATCH http://localhost:8080/orders/550e8400-e29b-41d4-a716-446655440000 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed"
  }'
```

## Makefile Commands

| Command | Description |
|---------|-------------|
| `make go` | Start services and install dependencies |
| `make go-hard` | Clean start (removes all data volumes) |
| `make up` | Start services |
| `make down` | Stop services |
| `make setup` | Install composer dependencies |
| `make sh` | Access PHP container shell |
| `make logs` | View all service logs |
| `make logs-api` | View API logs |
| `make logs-consumer` | View consumer logs |
| `make logs-rabbit` | View RabbitMQ logs |
| `make log` | View application log file (logs/app.log) |
| `make queue-status` | Show RabbitMQ queue status |
| `make postgres-cli` | Access PostgreSQL CLI |
| `make mongo-cli` | Access MongoDB CLI |
| `make health` | Check API health |
| `make create-order` | Create a test order |
| `make get-orders` | Get all orders |
| `make rabbitmq-ui` | Show RabbitMQ UI credentials |

## Monitoring

### View Application Logs

```bash
make log
```

This shows the Slim application logs from `logs/app.log`.

### View Consumer Logs

```bash
make logs-consumer
```

### View All Service Logs

```bash
make logs
```

### Check Queue Status

```bash
make queue-status
```

Shows queue names, message count, and connected consumers.

### RabbitMQ Queue Monitoring

Visit http://localhost:15672 and navigate to the "Queues" tab to see the `orders_events` queue.

## Development

### Rebuild Containers

```bash
docker compose down
docker compose up -d --build
```

### Run Composer Commands

```bash
docker compose exec php composer install
```

### Database Access

#### PostgreSQL (Write DB)
```bash
docker compose exec postgres psql -U postgres -d orders_write
```

#### MongoDB (Read DB)
```bash
docker compose exec mongodb mongosh -u mongo -p mongo --authenticationDatabase admin
```

## Testing the CQRS Flow

1. Create an order using the POST endpoint
2. Check RabbitMQ management UI - you should see the event being processed
3. Check consumer logs to see event consumption
4. Query the order using GET endpoint - data comes from MongoDB
5. Verify write data in PostgreSQL:
```sql
SELECT * FROM orders;
```

## Troubleshooting

### Consumer not processing events
- Check consumer logs: `docker compose logs consumer`
- Verify RabbitMQ is running: `docker compose ps rabbitmq`
- Check RabbitMQ management UI for queue status

### Database connection issues
- Ensure all containers are healthy: `docker compose ps`
- Check environment variables in docker-compose.yml
- Verify database credentials

### Port conflicts
- Stop services using ports 5432, 27017, 5672, 8080, or 15672
- Or modify the ports in docker-compose.yml

## Stopping the Application

```bash
docker compose down
```

To also remove volumes (delete all data):
```bash
docker compose down -v
```

## License

MIT

# Bruno API Collection - CQRS Order API

This directory contains a [Bruno](https://www.usebruno.com/) API collection for testing the CQRS Event-Driven Order Management System.

## What is Bruno?

Bruno is an open-source API client that stores collections directly in your filesystem using plain text files. It's an alternative to Postman/Insomnia.

## Installation

1. Download Bruno from [https://www.usebruno.com/](https://www.usebruno.com/)
2. Open Bruno
3. Click "Open Collection"
4. Navigate to this directory: `docs/bruno`

## Collection Structure

```
docs/bruno/
├── bruno.json                  # Collection metadata
├── environments/
│   └── Local.bru              # Local environment variables
├── Health Check.bru           # API health check endpoint
└── Orders/
    ├── Create Order.bru       # POST /orders (Command)
    ├── Update Order.bru       # PATCH /orders/{id} (Command)
    ├── Get All Orders.bru     # GET /orders (Query)
    └── Get Order by ID.bru    # GET /orders/{id} (Query)
```

## Available Endpoints

### Commands (Write Operations)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/orders` | POST | Create a new order |
| `/orders/{id}` | PATCH | Update an existing order |

### Queries (Read Operations)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/orders` | GET | Get all orders |
| `/orders/{id}` | GET | Get a specific order by ID |

## Usage

### 1. Start the Services

Make sure all Docker services are running:

```bash
cd ../..
docker compose up -d
```

### 2. Select Environment

In Bruno, select the "Local" environment which points to `http://localhost:8080`.

### 3. Test the Flow

#### Step 1: Health Check
Run the "Health Check" request to verify the API is running.

#### Step 2: Create an Order
1. Open "Orders > Create Order"
2. Review/modify the request body
3. Send the request
4. Copy the `id` from the response

#### Step 3: Update the Order
1. Open "Orders > Update Order"
2. Replace `{{orderId}}` in the URL with the ID from Step 2
3. Modify the request body (e.g., change status to "completed")
4. Send the request

#### Step 4: Get the Order
1. Open "Orders > Get Order by ID"
2. Replace `{{orderId}}` in the URL with the ID from Step 2
3. Send the request
4. Verify the data matches what you created/updated

#### Step 5: Get All Orders
1. Open "Orders > Get All Orders"
2. Send the request
3. See all orders in the system

## CQRS Flow Verification

When you create or update an order, you can verify the CQRS event flow:

1. **Command executed** → Order saved to PostgreSQL
2. **Event dispatched** → Event sent to RabbitMQ
3. **Consumer processes** → Data synced to MongoDB
4. **Query returns** → Data fetched from MongoDB

### Monitor the Flow

```bash
# Watch consumer logs
docker compose logs -f consumer

# Check RabbitMQ management UI
# Open http://localhost:15672
# Login: rabbitmq / rabbitmq

# Verify PostgreSQL (write DB)
docker compose exec postgres psql -U postgres -d orders_write -c "SELECT * FROM orders;"

# Verify MongoDB (read DB)
docker compose exec mongodb mongosh -u mongo -p mongo --authenticationDatabase admin orders_read --eval "db.orders.find().pretty()"
```

## Environment Variables

You can modify the environment file to point to different servers:

```
vars {
  baseUrl: http://localhost:8080
  # Or production: https://api.example.com
}
```

## Troubleshooting

### Service Not Running

If you get connection errors:
- Verify services are running: `docker compose ps`
- Check service logs: `docker compose logs php`

### Order Not Found

If you get 404 errors:
- Make sure you're using a valid order ID
- Check that the consumer is running and processing events
- Verify MongoDB has the data: See "Monitor the Flow" above

## Documentation

Each request includes detailed documentation accessible in Bruno's "Docs" tab, including:
- Request/response structure
- Parameter descriptions
- Example payloads
- Error responses

## Alternative: Using cURL

If you prefer command-line tools, you can also use cURL:

```bash
# Create Order
curl -X POST http://localhost:8080/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "items": [{"product": "Laptop", "quantity": 1, "price": 999.99}],
    "total_amount": 999.99
  }'

# Get All Orders
curl http://localhost:8080/orders

# Get Order by ID
curl http://localhost:8080/orders/{order-id}

# Update Order
curl -X PATCH http://localhost:8080/orders/{order-id} \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'
```

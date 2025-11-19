# Variables
DC=docker compose

.PHONY: go go-hard up setup down sh logs db-init db-reset postgres-cli mongo-cli \
        consumer health create-order get-orders rabbitmq-ui clear

%:
	@:

go:
	make down
	make up
	sleep 5
	make setup

go-hard:
	make down
	docker volume rm -f cqrs_postgres_data
	docker volume rm -f cqrs_mongodb_data
	docker volume rm -f cqrs_rabbitmq_data
	make up
	sleep 5
	make setup

up:
	$(DC) up -d --build

setup:
	$(DC) exec php composer install --ignore-platform-reqs

down:
	$(DC) down

sh:
	$(DC) exec php sh

logs:
	$(DC) logs -f --tail=10

db-init:
	@echo "Databases are auto-initialized on first run"

db-reset:
	make down
	docker volume rm -f cqrs_postgres_data
	docker volume rm -f cqrs_mongodb_data
	make up
	sleep 5

postgres-cli:
	$(DC) exec postgres psql -U postgres -d orders_write

mongo-cli:
	$(DC) exec mongodb mongosh orders_read -u mongo -p mongo

consumer:
	$(DC) logs -f consumer

rabbitmq-ui:
	@echo "RabbitMQ Management UI: http://localhost:15672"
	@echo "Username: rabbitmq"
	@echo "Password: rabbitmq"

clear:
	$(DC) exec php rm -rf api/vendor
	$(DC) exec php rm -rf api/composer.lock

health:
	@curl -s http://localhost:8080

create-order:
	@curl -X POST http://localhost:8080/orders \
	  -H "Content-Type: application/json" \
	  -d '{"customer_name":"John Doe","customer_email":"john@example.com","items":[{"product":"Laptop","quantity":1,"price":999.99}],"total_amount":999.99}'

get-orders:
	@curl -s http://localhost:8080/orders

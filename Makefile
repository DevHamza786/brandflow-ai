# PBOS development commands (Docker)
.PHONY: help up down build logs shell migrate horizon-status test

help:
	@echo "PBOS Docker commands:"
	@echo "  make up          Start stack (api, nginx, postgres, redis, horizon, scheduler)"
	@echo "  make up-tools    Start stack + pgAdmin"
	@echo "  make down        Stop stack"
	@echo "  make build       Build images"
	@echo "  make logs        Follow all logs"
	@echo "  make shell       Shell into API container"
	@echo "  make migrate     Run migrations"
	@echo "  make test        Run PHPUnit"

up:
	@bash docker/scripts/dev-up.sh

up-tools:
	@cp -n .env.docker.example .env 2>/dev/null || true
	@cp -n apps/api/.env.docker.example apps/api/.env 2>/dev/null || true
	docker compose --profile tools up -d --build

down:
	docker compose --profile tools down

build:
	docker compose build

logs:
	docker compose logs -f api nginx horizon scheduler postgres redis

shell:
	docker compose exec api bash

migrate:
	docker compose exec api php artisan migrate

migrate-pgvector:
	docker compose exec api php artisan migrate --path=database/migrations/pgvector

horizon-status:
	docker compose exec horizon php artisan horizon:status

test:
	docker compose exec api php artisan test

composer-install:
	docker compose exec api composer install

pint:
	docker compose exec api ./vendor/bin/pint

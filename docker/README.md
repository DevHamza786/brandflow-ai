# PBOS Docker Development Environment

Production-style service split optimized for local development with hot reload.

## Architecture

```text
                    ┌─────────────┐
   :8080 ──────────►│   nginx     │
                    └──────┬──────┘
                           │ fastcgi
                    ┌──────▼──────┐
                    │  api (FPM)  │◄── bind mount apps/api (hot reload)
                    └──────┬──────┘
           ┌───────────────┼───────────────┐
           │               │               │
    ┌──────▼──────┐ ┌──────▼──────┐ ┌─────▼─────┐
    │  postgres   │ │    redis    │ │  horizon  │
    │  (pgvector) │ │   (queues)  │ │ (workers) │
    └─────────────┘ └─────────────┘ └───────────┘
                           ▲
                    ┌──────┴──────┐
                    │  scheduler  │
                    └─────────────┘
```

| Service | Role | Port |
|---------|------|------|
| `nginx` | HTTP edge, static assets | 8080 |
| `api` | Laravel PHP-FPM | internal 9000 |
| `horizon` | Redis queue workers (all PBOS queues) | — |
| `scheduler` | `schedule:work` | — |
| `postgres` | PostgreSQL 16 + pgvector | 5432 |
| `redis` | Queues, cache, sessions | 6379 |
| `pgadmin` | DB UI (optional profile) | 5050 |

## Quick start

```bash
# From repository root
cp .env.docker.example .env
cp apps/api/.env.docker.example apps/api/.env

docker compose up -d --build
# or
make up
```

| URL | Description |
|-----|-------------|
| http://localhost:8080 | Laravel API |
| http://localhost:8080/horizon | Horizon dashboard |
| http://localhost:5050 | pgAdmin (`--profile tools`) |

## Queues (Horizon)

Workers process Redis queues in priority order:

`critical` → `scheduling` → `workflows` → `ai` → `scraping` → `analytics` → `default`

Configured in `apps/api/config/queues.php` and `apps/api/config/horizon.php`.

## Hot reload

- PHP source is bind-mounted: `./apps/api` → `/var/www/html`
- Opcache revalidates on every request (`docker/php/opcache.dev.ini`)
- Run Composer inside the container:

```bash
docker compose exec api composer install
```

## Common commands

```bash
docker compose logs -f horizon
docker compose exec api php artisan migrate
docker compose exec api php artisan test
docker compose exec api php artisan horizon:status
make shell
make down
```

## pgvector

PostgreSQL starts with `vector` extension via `docker/postgres/init/01-extensions.sql`.

Enable Laravel pgvector migrations:

```bash
RUN_PGVECTOR_MIGRATIONS=true   # in .env / apps/api/.env
docker compose exec api php artisan migrate --path=database/migrations/pgvector
```

## Files

| Path | Purpose |
|------|---------|
| `docker-compose.yml` | Service definitions, networks, volumes |
| `docker/api/Dockerfile` | PHP 8.3 FPM + extensions (pcntl, redis, pgsql) |
| `docker/nginx/default.conf` | Nginx → PHP-FPM |
| `docker/supervisor/*.conf` | Reference configs (Compose uses separate services) |
| `docker/scripts/` | Entrypoints, wait-for-deps, dev helpers |
| `.env.docker.example` | Compose variables |
| `apps/api/.env.docker.example` | Laravel variables |

## Windows notes

- Use Docker Desktop with WSL2 backend for best bind-mount performance.
- Horizon requires **Linux containers** (pcntl is available in the image; not on native Windows PHP).
- This stack runs Horizon inside Linux containers — no local `ext-pcntl` needed on the host.

## Production

Build production image:

```bash
docker build -f docker/api/Dockerfile --target production -t pbos-api:latest .
```

Deploy `api`, `horizon`, and `scheduler` as separate replicas (ECS/K8s), matching this Compose layout.

# PBOS API (Laravel)

Backend for the AI-powered personal branding operating system.

## Stack

- Laravel 13
- PostgreSQL
- Redis (queues, cache, vectors)
- Modular domains under `app/Domains/`

## Documentation

| Doc | Description |
|-----|-------------|
| [app/Domains/README.md](app/Domains/README.md) | Domain module layout |
| [docs/AGENTS.md](../docs/AGENTS.md) | Agent catalog and coding rules |
| [docs/PROJECT_ARCHITECTURE.md](../docs/PROJECT_ARCHITECTURE.md) | System architecture |

## Configuration

| File | Purpose |
|------|---------|
| `config/domains.php` | Domain service provider registry |
| `config/agents.php` | Agent slugs, queues, class bindings |
| `config/queues.php` | PBOS queue names and timeouts |

## Development

### Docker (recommended)

Full stack: PostgreSQL, Redis, Nginx, Horizon, Scheduler.

```bash
# From repository root
cp .env.docker.example .env
cp apps/api/.env.docker.example apps/api/.env
docker compose up -d --build
```

API: http://localhost:8080 — see [docker/README.md](../../docker/README.md) and `Makefile`.

### Local (without Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Queue workers (Redis):

```bash
# Linux / macOS / WSL
php artisan horizon

# Windows host (no pcntl)
php artisan queue:work redis --queue=critical,scheduling,workflows,ai,scraping,analytics,default
```

Horizon dashboard: `/horizon` (local: open access; production: configure gate in `HorizonServiceProvider`).

Queue infrastructure: [app/Queue/README.md](app/Queue/README.md)

## Architecture (summary)

- **Thin controllers** → Actions/Services → Repositories
- **AI** via `App\Domains\AI\Contracts\LlmGateway` only
- **Agents** via `App\Domains\Agents\Contracts\AgentContract` + `RunAgentJob`
- **No business logic** in controllers or repositories

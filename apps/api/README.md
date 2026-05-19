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

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Queue worker (when jobs are implemented):

```bash
php artisan queue:work --queue=critical,ai,scrape,analytics,default
```

## Architecture (summary)

- **Thin controllers** → Actions/Services → Repositories
- **AI** via `App\Domains\AI\Contracts\LlmGateway` only
- **Agents** via `App\Domains\Agents\Contracts\AgentContract` + `RunAgentJob`
- **No business logic** in controllers or repositories

# Domains (Modular Monolith)

PBOS backend logic is organized by **bounded context** under `app/Domains/`.

## Layout per domain

```
{Domain}/
├── Actions/         # Single-use-case orchestrators (execute())
├── Contracts/       # Repository and service interfaces
├── Data/            # DTOs extending Shared\Data\DataTransferObject
├── Events/
├── Jobs/            # Domain-specific queued jobs
├── Models/          # Eloquent models (persistence only)
├── Policies/
├── Providers/       # {Domain}ServiceProvider
├── Repositories/    # Implements Contracts; DB access only
├── Services/        # Business rules and orchestration
└── README.md
```

## Cross-cutting

| Path | Purpose |
|------|---------|
| `Shared/` | Base DTO, `BaseQueueJob`, repository contracts |
| `AI/` | `LlmGateway`, prompts, embeddings |
| `Agents/` | `AgentContract`, `RunAgentJob`, agent implementations |

## Architecture rules

1. Controllers stay thin; no business logic.
2. Services own orchestration; repositories own queries.
3. LLM calls only through `AI\Contracts\LlmGateway`.
4. Async AI/scrape/publish via Redis queues.
5. Agents are isolated; coordinate via Workflows or events.

## Registration

Domain providers are listed in `config/domains.php` and loaded by `App\Providers\DomainsServiceProvider`.

## Documentation

- [docs/AGENTS.md](../../../docs/AGENTS.md) — agent catalog and coding workflow
- [docs/PROJECT_ARCHITECTURE.md](../../../docs/PROJECT_ARCHITECTURE.md) — system design

# Queue & Workflow Orchestration

Cross-cutting async infrastructure for PBOS.

## Layout

| Path | Purpose |
|------|---------|
| `Enums/QueueName.php` | Typed queue names |
| `Jobs/AbstractQueueJob.php` | Base job (retry, timeout, tags, failed handler) |
| `WorkflowJobs/` | Workflow DAG jobs (`workflows` queue) |
| `Middleware/` | Job middleware (retry, logging, workflow tracking) |
| `Pipelines/` | Workflow step execution pipeline |
| `Support/` | `JobTagger`, `RetryPolicyResolver` |
| `Failed/` | Central `QueueFailedJobHandler` |

## Queues (Redis)

Priority order: `critical` → `scheduling` → `workflows` → `ai` → `scraping` → `analytics` → `default`

Config: `config/queues.php`, Horizon: `config/horizon.php`

## Running workers

```bash
# Production / Linux
php artisan horizon

# Local fallback (no pcntl / Windows)
php artisan queue:work redis --queue=critical,scheduling,workflows,ai,scraping,analytics,default
```

## Workflow tracking

Redis hash: `{env}:pbos:{workspace_id}:workflow:run:{run_id}`

Service: `App\Domains\Workflows\Services\WorkflowExecutionTracker`

## Related docs

- [docs/AGENTS.md](../../../docs/AGENTS.md) §7
- [docs/PROJECT_ARCHITECTURE.md](../../../docs/PROJECT_ARCHITECTURE.md) §4

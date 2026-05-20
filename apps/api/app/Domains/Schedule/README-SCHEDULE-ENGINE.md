# Scheduling engine & cron orchestration (BrandFlow / PBOS)

Backend-only automation spine for **timezone-stored** publishes, **queue-driven** execution, and **analytics-ready** execution events (no dashboards here).

## Concepts

| Piece | Role |
|-------|------|
| `SchedulePostAction` | Single write path: immediate → `queued` + `PublishLinkedInPostJob`; future → `scheduled`, **no** Redis delayed duplicate. |
| `ProcessScheduledPostsJob` | Horizon job on `orchestration` queue: DB claim + fan-out publish workers. |
| `SchedulerOrchestrationService` | Transactional `claimDueScheduledPosts` + pipeline dispatch. |
| `ScheduleExecutionPipeline` | `PublishLinkedInPostJob::dispatch` after successful row claim TX + logging. |
| `ScheduleExecutionLogger` | Structured `Log::info` + `schedule_execution_events` rows (safe no-op if DB missing). |
| `schedule:orchestrate` | Scheduler entry (every minute, `withoutOverlapping`) → dispatches `ProcessScheduledPostsJob`. |
| `schedule:recover-stale-queued` | Hourly safety net for stuck `queued` rows. |

## Idempotency / no double publish

- **Future vs immediate split** removes “delayed job + cron” race.
- **`claimDueScheduledPosts`** uses `lockForUpdate` + moves `scheduled → queued` atomically.
- **Publish fan-out** occurs only after the claim transaction commits (`SchedulerOrchestrationService`), so workers never see phantom `queued` rows mid-rollback.
- **`LinkedInPublishingService`** already short-circuits published rows and uses `Cache::lock` per publish attempt.

## Cron (Docker)

The `scheduler` service runs `php artisan schedule:work`, which executes Laravel’s schedule (see `routes/console.php`).

Verification:

```bash
php artisan schedule:list
php artisan schedule:run   # dry tick in dev — prefer schedule:work long-running
php artisan schedule:orchestrate
```

## Configuration

`config/scheduling.php`:

- `orchestrator_workspace_id` — Horizon tag namespace for global batch jobs.
- `orchestration_batch_limit` — max rows claimed per tick.
- `stale_queued_ttl_minutes` / `recovery_batch_limit`.

## Multi-platform / recurrence / workflows (foundation)

`scheduled_posts.platform`, `schedule_pattern`, `recurrence_rule` (JSON), `series_id`, `workflow_run_id`, `orchestration_metadata` are persisted for downstream engines; expansion is **data-only** in this phase.

## Related

- Publish contract: `README-PUBLISHING.md`
- Queues: `config/queues.php`, `config/horizon.php` (`orchestration` before `scheduling` in production supervisor).

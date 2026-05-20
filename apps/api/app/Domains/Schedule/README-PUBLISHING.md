# LinkedIn publishing (queue architecture)

## Design

- **Table**: `scheduled_posts` stores `workspace_id`, `linkedin_integration_id`, `generated_output_id`, `content`, `scheduled_for`, `published_at`, `provider_post_id`, `status`, `metadata`, `error_details`, plus existing scheduling fields (`publish_at`, `linkedin_urn`, …).
- **Queue**: `PublishLinkedInPostJob` runs on **`scheduling`** (`QueueName::Scheduling`). Retry policy comes from `config/queues.php`.
- **Orchestration**: `SchedulePostAction` (via `PublishToLinkedInAction`) persists a `ScheduledPost`. **Immediate** publishes (`scheduled_for` not in the future) → status `queued` + `PublishLinkedInPostJob` now. **Future** publishes → status `scheduled` only; **`ProcessScheduledPostsJob`** (cron → `orchestration` queue) atomically claims due rows then dispatches publish jobs — avoids duplicate delayed + cron execution. See `README-SCHEDULE-ENGINE.md`.
- **Provider**: `SocialPublishingProviderContract` → `LinkedInSocialPublisher` (UGC `/v2/ugcPosts`). Swap implementation or add a registry later for multi-platform.
- **State**: `LinkedInPublishingService` drives `queued` → `publishing` → `published` / `failed`; idempotent skip when already published with `provider_post_id`.
- **Non-retryable errors**: `UnretryablePublishingException` (4xx except 429) marks the row `failed` and the job **exits without retry** after persisting `error_details`.
- **Concurrency**: Redis cache lock `scheduled_post.publish:{workspace_id}:{id}`; lock contention throws a transient error so the duplicate job retries.
- **Cron hook**: `php artisan schedule:orchestrate` (Laravel Scheduler every minute) dispatches `ProcessScheduledPostsJob`. Legacy `schedule:dispatch-due` forwards to the same job. `schedule:recover-stale-queued` re-queues stuck `queued` rows hourly.
- **UI**: `GET /api/v1/scheduled-posts` returns recent rows + `in_flight` for polling; web route **`/integrations/posts`** shows status, LinkedIn `provider_post_id`, and a best-effort “Open on LinkedIn” link.
- **Workflows**: `PublishingWorkflowIntegration::queueAfterWorkflow()` persists `workflow_run_id` on the row and metadata `source=workflow`.

## Manual verification

1. **Migrate** (PostgreSQL): `php artisan migrate`
2. **Worker**: Horizon (or `php artisan queue:work redis --queue=orchestration,scheduling`)
3. **Dispatch** (after inserting a row via action or tinker):  
   `php artisan publish:trace-linkedin {workspace_id} {scheduled_post_id}`
4. **Web app**: open `/integrations` (or `/settings/integrations`) — after connecting LinkedIn, use **Queue LinkedIn publish (dev)** to call `POST /api/v1/publish/linkedin` with `VITE_DEFAULT_WORKSPACE_ID` from env (same as other pages).

## Automated test

Feature test `tests/Feature/PublishLinkedInTest.php` asserts the API returns `202` and pushes `PublishLinkedInPostJob` (run in Docker: `php artisan test tests/Feature/PublishLinkedInTest.php`).

Do not rely on LinkedIn API stability in sandbox / pre-approval apps — the important property is **persisted provider post id + timestamps + metadata** for future analytics.

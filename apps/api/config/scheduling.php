<?php

declare(strict_types=1);

/**
 * Scheduling engine — cron orchestration, batch sizing, stale recovery (BrandFlow PBOS).
 */
return [

    'orchestrator_workspace_id' => env(
        'SCHEDULE_ORCHESTRATOR_WORKSPACE_ID',
        '00000000-0000-0000-0000-000000000000',
    ),

    /*
    | Max rows claimed per orchestration cycle (cron + ProcessScheduledPostsJob).
    */
    'orchestration_batch_limit' => (int) env('SCHEDULE_ORCHESTRATION_BATCH', 100),

    /*
    | Cache lock TTL (seconds) when claiming individual posts through the pipeline.
    */
    'claim_lock_ttl' => (int) env('SCHEDULE_CLAIM_LOCK_TTL', 30),

    /*
    | Queued rows stuck without progressing to publishing (minutes) → recovery eligible.
    */
    'stale_queued_ttl_minutes' => (int) env('SCHEDULE_STALE_QUEUED_MINUTES', 45),

    /*
    | Max automatic recovery publishes per artisan run / hour.
    */
    'recovery_batch_limit' => (int) env('SCHEDULE_RECOVERY_BATCH', 25),

];

<?php

declare(strict_types=1);

/**
 * PBOS queue topology, retry policies, and Redis settings.
 *
 * @see docs/AGENTS.md §7
 * @see docs/PROJECT_ARCHITECTURE.md §4.1
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Redis connection used for all PBOS queues (Horizon + dispatch).
    |--------------------------------------------------------------------------
    */
    'redis_connection' => env('REDIS_QUEUE_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Queue names (priority order for Horizon supervisors).
    |--------------------------------------------------------------------------
    */
    'names' => [
        'critical',
        'orchestration',
        'scheduling',
        'workflows',
        'ai',
        'scraping',
        'analytics',
        'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-queue timeouts (seconds) — must be < queue.connections.redis.retry_after
    |--------------------------------------------------------------------------
    */
    'timeouts' => [
        'critical' => 90,
        'orchestration' => 120,
        'scheduling' => 90,
        'workflows' => 120,
        'ai' => 120,
        'scraping' => 180,
        'analytics' => 90,
        'default' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry policies: tries, exponential backoff (seconds), max exceptions
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'critical' => [
            'tries' => 5,
            'backoff' => [10, 30, 60, 120, 300],
        ],
        'orchestration' => [
            'tries' => 3,
            'backoff' => [10, 30, 120],
        ],
        'scheduling' => [
            'tries' => 5,
            'backoff' => [10, 30, 60, 120, 300],
        ],
        'workflows' => [
            'tries' => 3,
            'backoff' => [15, 60, 180],
        ],
        'ai' => [
            'tries' => 3,
            'backoff' => [10, 60, 300],
        ],
        'scraping' => [
            'tries' => 3,
            'backoff' => [30, 120, 300],
        ],
        'analytics' => [
            'tries' => 3,
            'backoff' => [10, 60, 180],
        ],
        'default' => [
            'tries' => 3,
            'backoff' => [10, 60, 300],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow execution tracking (Redis hash TTL in seconds).
    |--------------------------------------------------------------------------
    */
    'workflow' => [
        'state_ttl' => (int) env('WORKFLOW_STATE_TTL', 604800), // 7 days
        'key_prefix' => env('QUEUE_KEY_PREFIX', null), // defaults to app.env:pbos
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed job handling
    |--------------------------------------------------------------------------
    */
    'failed' => [
        'log_channel' => env('QUEUE_FAILED_LOG_CHANNEL', 'stack'),
        'alert_on_queues' => ['critical', 'orchestration', 'scheduling'],
    ],

];

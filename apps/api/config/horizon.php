<?php

declare(strict_types=1);

use Illuminate\Support\Str;

$pbosQueues = config('queues.names', [
    'critical',
    'scheduling',
    'workflows',
    'ai',
    'scraping',
    'analytics',
    'default',
]);

$redisConnection = config('queues.redis_connection', 'default');

$waitThresholds = [];
foreach ($pbosQueues as $queue) {
    $waitThresholds["redis:{$queue}"] = 60;
}

return [

    'name' => env('HORIZON_NAME', env('APP_NAME', 'pbos')),

    'domain' => env('HORIZON_DOMAIN'),

    'path' => env('HORIZON_PATH', 'horizon'),

    'use' => $redisConnection,

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'
    ),

    'middleware' => ['web'],

    'waits' => $waitThresholds,

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    'silenced' => [],

    'silenced_tags' => [],

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => env('HORIZON_FAST_TERMINATION', false),

    'memory_limit' => (int) env('HORIZON_MEMORY_LIMIT', 128),

    /*
    |--------------------------------------------------------------------------
    | Supervisor defaults
    |--------------------------------------------------------------------------
    | Local: single supervisor, all queues. Production: split supervisors (below).
    */
    'defaults' => env('APP_ENV') === 'production' ? [
        'supervisor-critical' => [
            'connection' => 'redis',
            'queue' => ['critical'],
            'balance' => 'simple',
            'maxProcesses' => (int) env('HORIZON_CRITICAL_WORKERS', 2),
            'memory' => 128,
            'tries' => 1,
            'timeout' => config('queues.timeouts.critical', 90),
            'nice' => 0,
        ],
        'supervisor-scheduling' => [
            'connection' => 'redis',
            'queue' => ['orchestration', 'scheduling'],
            'balance' => 'simple',
            'maxProcesses' => (int) env('HORIZON_SCHEDULING_WORKERS', 2),
            'memory' => 128,
            'tries' => 1,
            'timeout' => config('queues.timeouts.scheduling', 90),
            'nice' => 0,
        ],
        'supervisor-workflows' => [
            'connection' => 'redis',
            'queue' => ['workflows'],
            'balance' => 'auto',
            'maxProcesses' => (int) env('HORIZON_WORKFLOW_WORKERS', 4),
            'memory' => 256,
            'tries' => 1,
            'timeout' => config('queues.timeouts.workflows', 120),
            'nice' => 0,
        ],
        'supervisor-ai' => [
            'connection' => 'redis',
            'queue' => ['ai'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => (int) env('HORIZON_AI_WORKERS', 8),
            'balanceMaxShift' => 2,
            'balanceCooldown' => 3,
            'memory' => 256,
            'tries' => 1,
            'timeout' => config('queues.timeouts.ai', 120),
            'nice' => 0,
        ],
        'supervisor-scraping' => [
            'connection' => 'redis',
            'queue' => ['scraping'],
            'balance' => 'simple',
            'maxProcesses' => (int) env('HORIZON_SCRAPING_WORKERS', 4),
            'memory' => 512,
            'tries' => 1,
            'timeout' => config('queues.timeouts.scraping', 180),
            'nice' => 0,
        ],
        'supervisor-analytics' => [
            'connection' => 'redis',
            'queue' => ['analytics'],
            'balance' => 'simple',
            'maxProcesses' => (int) env('HORIZON_ANALYTICS_WORKERS', 2),
            'memory' => 128,
            'tries' => 1,
            'timeout' => config('queues.timeouts.analytics', 90),
            'nice' => 0,
        ],
        'supervisor-default' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'simple',
            'maxProcesses' => (int) env('HORIZON_DEFAULT_WORKERS', 2),
            'memory' => 128,
            'tries' => 1,
            'timeout' => config('queues.timeouts.default', 60),
            'nice' => 0,
        ],
    ] : [
        'supervisor-local' => [
            'connection' => 'redis',
            'queue' => $pbosQueues,
            'balance' => 'auto',
            'maxProcesses' => (int) env('HORIZON_LOCAL_WORKERS', 3),
            'memory' => 256,
            'tries' => 1,
            'timeout' => 180,
            'nice' => 0,
        ],
    ],

    'environments' => [
        'production' => [],
        'local' => [],
    ],

    'watch' => [
        'app',
        'bootstrap',
        'config/**/*.php',
        'database/**/*.php',
        'routes',
        'composer.lock',
        'composer.json',
        '.env',
    ],
];

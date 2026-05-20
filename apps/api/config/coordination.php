<?php

declare(strict_types=1);

/**
 * Multi-agent coordination — routing, priorities, failure isolation.
 */
return [

    'default_correlation_key' => 'coordination:workspace:default',

    'failure_isolation' => true,

    'dispatch_agents' => (bool) env('COORDINATION_DISPATCH_AGENTS', false),

    'default_cycle_tasks' => [
        ['task_type' => 'strategist_plan', 'role' => 'strategist', 'priority' => 10],
        ['task_type' => 'analytics_insights', 'role' => 'analytics', 'priority' => 20],
        ['task_type' => 'optimization_cycle', 'role' => 'optimization', 'priority' => 30],
        ['task_type' => 'recommendation_generate', 'role' => 'recommendation', 'priority' => 40],
        ['task_type' => 'competitor_analysis', 'role' => 'competitor', 'priority' => 50],
        ['task_type' => 'publishing_schedule', 'role' => 'publishing', 'priority' => 60],
    ],

    'role_routing' => [
        'strategist' => [
            'handler' => 'agent',
            'agent_slug' => 'hook',
            'fallback_agent_slug' => 'profile',
        ],
        'analytics' => [
            'handler' => 'agent',
            'agent_slug' => 'analytics',
        ],
        'optimization' => [
            'handler' => 'optimization',
        ],
        'competitor' => [
            'handler' => 'agent',
            'agent_slug' => 'competitor',
        ],
        'publishing' => [
            'handler' => 'publishing',
        ],
        'recommendation' => [
            'handler' => 'recommendation',
        ],
    ],

    'task_routing' => [
        'strategist_plan' => 'strategist',
        'analytics_insights' => 'analytics',
        'optimization_cycle' => 'optimization',
        'competitor_analysis' => 'competitor',
        'publishing_schedule' => 'publishing',
        'recommendation_generate' => 'recommendation',
        'hook_generation' => 'strategist',
    ],

    'ml' => [
        'enabled' => (bool) env('COORDINATION_ML_ENABLED', false),
        'experiment_bucket' => 'coordination_v1',
    ],

    /** Comma-separated task types to force-fail (testing only). */
    'test_force_fail_tasks' => array_values(array_filter(array_map(
        static fn (string $v): string => trim($v),
        explode(',', (string) env('COORDINATION_TEST_FORCE_FAIL', '')),
    ))),

];

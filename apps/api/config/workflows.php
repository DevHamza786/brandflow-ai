<?php

declare(strict_types=1);

/**
 * Built-in workflow DAG definitions (seeded per workspace on first use).
 */
return [

    'definitions' => [

        'hook_generation' => [
            'name' => 'Hook Generation',
            'steps' => [
                ['id' => 'validate_input', 'type' => 'system'],
                ['id' => 'dispatch_agent', 'type' => 'system'],
                ['id' => 'run_hook_agent', 'type' => 'agent', 'agent_slug' => 'hook'],
                ['id' => 'persist_results', 'type' => 'system'],
            ],
        ],

    ],

];

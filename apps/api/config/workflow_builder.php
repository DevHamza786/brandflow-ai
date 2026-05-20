<?php

declare(strict_types=1);

return [

    'default_blueprint_slug' => 'multi-agent-default',

    'node_handlers' => [
        'agent' => 'agent',
        'delay' => 'delay',
        'condition' => 'condition',
        'optimization' => 'optimization',
        'autonomous' => 'autonomous',
        'coordination' => 'coordination',
        'human_gate' => 'human_gate',
    ],

    'ml' => [
        'enabled' => (bool) env('WORKFLOW_BUILDER_ML_ENABLED', false),
    ],

];

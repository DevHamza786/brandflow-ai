<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Enums;

enum WorkflowNodeType: string
{
    case Agent = 'agent';
    case Delay = 'delay';
    case Condition = 'condition';
    case Optimization = 'optimization';
    case Autonomous = 'autonomous';
    case Coordination = 'coordination';
    case HumanGate = 'human_gate';
}

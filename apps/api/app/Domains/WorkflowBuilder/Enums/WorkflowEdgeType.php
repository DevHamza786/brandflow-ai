<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Enums;

enum WorkflowEdgeType: string
{
    case Default = 'default';
    case Conditional = 'conditional';
    case Delay = 'delay';
}

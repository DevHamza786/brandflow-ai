<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Enums;

enum AutonomousWorkflowMode: string
{
    case Observe = 'observe';
    case Suggest = 'suggest';
    case Execute = 'execute';
}

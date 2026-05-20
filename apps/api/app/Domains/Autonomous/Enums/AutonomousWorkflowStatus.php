<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Enums;

enum AutonomousWorkflowStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Disabled = 'disabled';
}

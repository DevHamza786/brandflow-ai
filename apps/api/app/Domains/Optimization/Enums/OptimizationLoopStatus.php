<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Enums;

enum OptimizationLoopStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Archived = 'archived';
}

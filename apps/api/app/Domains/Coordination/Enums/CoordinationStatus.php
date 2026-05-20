<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Enums;

enum CoordinationStatus: string
{
    case Active = 'active';
    case Running = 'running';
    case PartialSuccess = 'partial_success';
    case Failed = 'failed';
    case Completed = 'completed';
    case Paused = 'paused';
}

<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Enums;

enum CoordinationSnapshotStatus: string
{
    case Pending = 'pending';
    case Routed = 'routed';
    case Dispatched = 'dispatched';
    case Completed = 'completed';
    case Failed = 'failed';
    case Recovered = 'recovered';
    case Skipped = 'skipped';
}

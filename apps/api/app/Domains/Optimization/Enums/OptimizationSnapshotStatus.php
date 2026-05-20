<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Enums;

enum OptimizationSnapshotStatus: string
{
    case Proposed = 'proposed';
    case Applied = 'applied';
    case Measured = 'measured';
    case Superseded = 'superseded';
}

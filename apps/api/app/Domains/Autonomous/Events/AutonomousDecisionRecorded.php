<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Events;

use App\Domains\Autonomous\Data\AutonomousExecutionSnapshotDto;
use Illuminate\Foundation\Events\Dispatchable;

final class AutonomousDecisionRecorded
{
    use Dispatchable;

    public function __construct(
        public readonly AutonomousExecutionSnapshotDto $snapshot,
    ) {
    }
}

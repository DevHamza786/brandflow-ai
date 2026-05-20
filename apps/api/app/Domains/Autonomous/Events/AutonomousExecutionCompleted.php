<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Events;

use App\Domains\Autonomous\Data\AutonomousWorkflowDto;
use Illuminate\Foundation\Events\Dispatchable;

final class AutonomousExecutionCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly AutonomousWorkflowDto $workflow,
        public readonly int $cycleNumber,
        public readonly int $snapshotsCreated,
        public readonly int $blockedCount,
    ) {
    }
}

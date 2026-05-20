<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<AutonomousExecutionSnapshotDto>  $snapshots
 * @param  array<string, int>  $countsByStatus
 */
final class RunAutonomousExecutionResultDto extends DataTransferObject
{
    public function __construct(
        public readonly AutonomousWorkflowDto $workflow,
        public readonly int $cycleNumber,
        public readonly int $snapshotsCreated,
        public readonly int $blockedCount,
        public readonly int $approvedCount,
        public readonly array $snapshots,
        public readonly array $countsByStatus,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'workflow' => $this->workflow->toArray(),
            'cycle_number' => $this->cycleNumber,
            'snapshots_created' => $this->snapshotsCreated,
            'blocked_count' => $this->blockedCount,
            'approved_count' => $this->approvedCount,
            'counts_by_status' => $this->countsByStatus,
            'snapshots' => array_map(static fn (AutonomousExecutionSnapshotDto $s) => $s->toArray(), $this->snapshots),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<OptimizationSnapshotDto>  $snapshots
 * @param  array<string, int>  $countsByEngine
 */
final class RunOptimizationCycleResultDto extends DataTransferObject
{
    public function __construct(
        public readonly OptimizationLoopDto $loop,
        public readonly int $cycleNumber,
        public readonly int $snapshotsCreated,
        public readonly int $recommendationsSynced,
        public readonly array $snapshots,
        public readonly array $countsByEngine,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'loop' => $this->loop->toArray(),
            'cycle_number' => $this->cycleNumber,
            'snapshots_created' => $this->snapshotsCreated,
            'recommendations_synced' => $this->recommendationsSynced,
            'counts_by_engine' => $this->countsByEngine,
            'snapshots' => array_map(static fn (OptimizationSnapshotDto $s) => $s->toArray(), $this->snapshots),
        ];
    }
}

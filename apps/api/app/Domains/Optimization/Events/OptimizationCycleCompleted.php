<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Events;

use App\Domains\Optimization\Data\OptimizationLoopDto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OptimizationCycleCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly OptimizationLoopDto $loop,
        public readonly int $cycleNumber,
        public readonly int $snapshotsCreated,
        public readonly int $recommendationsSynced,
    ) {
    }
}

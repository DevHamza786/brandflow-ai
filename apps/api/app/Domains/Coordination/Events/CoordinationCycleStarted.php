<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CoordinationCycleStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $workspaceId,
        public readonly string $coordinationId,
        public readonly int $cycleNumber,
        public readonly string $traceId,
    ) {
    }
}

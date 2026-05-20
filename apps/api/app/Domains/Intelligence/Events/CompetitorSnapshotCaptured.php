<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CompetitorSnapshotCaptured
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $workspaceId,
        public readonly string $competitorId,
        public readonly string $snapshotId,
    ) {
    }
}

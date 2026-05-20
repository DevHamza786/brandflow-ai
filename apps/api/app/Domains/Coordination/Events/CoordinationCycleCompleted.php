<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Events;

use App\Domains\Coordination\Data\RunCoordinationCycleResultDto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CoordinationCycleCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $workspaceId,
        public readonly string $coordinationId,
        public readonly RunCoordinationCycleResultDto $result,
    ) {
    }
}

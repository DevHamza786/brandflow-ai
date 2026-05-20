<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<string>  $completedTasks
 * @param  list<string>  $failedTasks
 */
final class RunCoordinationCycleResultDto extends DataTransferObject
{
    public function __construct(
        public readonly string $coordinationId,
        public readonly int $cycleNumber,
        public readonly int $snapshotsCreated,
        public readonly int $tasksCompleted,
        public readonly int $tasksFailed,
        public readonly int $tasksRecovered,
        public readonly array $completedTasks,
        public readonly array $failedTasks,
        public readonly string $traceId,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class OrchestrationBatchResultDto extends DataTransferObject
{
    /**
     * @param  list<DispatchScheduledPostResultDto>  $dispatches
     */
    public function __construct(
        public readonly string $traceId,
        public readonly int $claimedCount,
        public readonly array $dispatches,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class DispatchScheduledPostResultDto extends DataTransferObject
{
    /**
     * @param  non-empty-string|null  $skippedReason
     */
    public function __construct(
        public readonly string $scheduledPostId,
        public readonly string $workspaceId,
        public readonly bool $dispatched,
        public readonly ?string $executionId,
        public readonly ?string $skippedReason,
    ) {
    }
}

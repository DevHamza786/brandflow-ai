<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Data;

use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * Append-only analytics event (maps to `analytics_events`).
 *
 * @param  array<string, mixed>  $properties
 */
final class CreateAnalyticsEventDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $eventType,
        public readonly ?string $entityType = null,
        public readonly ?string $entityId = null,
        public readonly array $properties = [],
        public readonly ?CarbonInterface $occurredAt = null,
        public readonly ?string $idempotencyKey = null,
        public readonly ?int $userId = null,
        public readonly ?string $sessionId = null,
    ) {
    }
}

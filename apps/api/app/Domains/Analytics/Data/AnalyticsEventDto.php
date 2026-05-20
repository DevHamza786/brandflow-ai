<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Data;

use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $properties
 */
final class AnalyticsEventDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $eventType,
        public readonly ?string $entityType,
        public readonly ?string $entityId,
        public readonly array $properties,
        public readonly CarbonInterface $occurredAt,
        public readonly ?string $idempotencyKey,
        public readonly ?int $userId,
        public readonly ?string $sessionId,
        public readonly ?CarbonInterface $createdAt = null,
    ) {
    }
}

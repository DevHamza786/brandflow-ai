<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Data;

use App\Domains\Coordination\Enums\CoordinationHandlerType;
use App\Domains\Coordination\Enums\CoordinationSnapshotStatus;
use App\Domains\Coordination\Enums\CoordinationSnapshotType;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  array<string, mixed>  $contextRefs
 * @param  array<string, mixed>  $payload
 * @param  array<string, mixed>|null  $error
 */
final class CreateCoordinationSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $agentCoordinationId,
        public readonly CoordinationSnapshotType $snapshotType,
        public readonly int $cycleNumber,
        public readonly CoordinationSnapshotStatus $status,
        public readonly ?string $roleSlug = null,
        public readonly ?string $taskType = null,
        public readonly ?string $agentSlug = null,
        public readonly ?string $routedAgentSlug = null,
        public readonly ?CoordinationHandlerType $handlerType = null,
        public readonly array $contextRefs = [],
        public readonly array $payload = [],
        public readonly ?array $error = null,
        public readonly ?string $idempotencyKey = null,
        public readonly ?string $traceId = null,
        public readonly ?string $agentRunId = null,
        public readonly int $priority = 100,
        public readonly ?int $durationMs = null,
    ) {
    }
}

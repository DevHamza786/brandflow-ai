<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Data;

use App\Domains\Coordination\Enums\CoordinationHandlerType;
use App\Domains\Coordination\Enums\CoordinationSnapshotStatus;
use App\Domains\Coordination\Enums\CoordinationSnapshotType;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $contextRefs
 * @param  array<string, mixed>  $payload
 * @param  array<string, mixed>|null  $error
 */
final class AgentCoordinationSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $agentCoordinationId,
        public readonly CoordinationSnapshotType $snapshotType,
        public readonly int $cycleNumber,
        public readonly ?string $roleSlug,
        public readonly ?string $taskType,
        public readonly ?string $agentSlug,
        public readonly ?string $routedAgentSlug,
        public readonly ?CoordinationHandlerType $handlerType,
        public readonly CoordinationSnapshotStatus $status,
        public readonly array $contextRefs,
        public readonly array $payload,
        public readonly ?array $error,
        public readonly ?string $traceId,
        public readonly ?string $agentRunId,
        public readonly int $priority,
        public readonly ?int $durationMs,
        public readonly CarbonInterface $createdAt,
    ) {
    }
}

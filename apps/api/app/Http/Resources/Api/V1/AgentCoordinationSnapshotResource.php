<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Coordination\Data\AgentCoordinationSnapshotDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read AgentCoordinationSnapshotDto $resource */
final class AgentCoordinationSnapshotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'agent_coordination_id' => $dto->agentCoordinationId,
            'snapshot_type' => $dto->snapshotType->value,
            'cycle_number' => $dto->cycleNumber,
            'role_slug' => $dto->roleSlug,
            'task_type' => $dto->taskType,
            'routed_agent_slug' => $dto->routedAgentSlug,
            'handler_type' => $dto->handlerType?->value,
            'status' => $dto->status->value,
            'context_refs' => $dto->contextRefs,
            'payload' => $dto->payload,
            'trace_id' => $dto->traceId,
            'created_at' => $dto->createdAt->toIso8601String(),
        ];
    }
}

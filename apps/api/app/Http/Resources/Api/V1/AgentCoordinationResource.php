<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Coordination\Data\AgentCoordinationDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read AgentCoordinationDto $resource */
final class AgentCoordinationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'workspace_id' => $dto->workspaceId,
            'status' => $dto->status->value,
            'coordination_mode' => $dto->coordinationMode->value,
            'correlation_key' => $dto->correlationKey,
            'current_cycle' => $dto->currentCycle,
            'workflow_run_id' => $dto->workflowRunId,
            'workflow_blueprint_id' => $dto->workflowBlueprintId,
            'shared_context' => $dto->sharedContext,
            'config' => $dto->config,
            'ml_state' => $dto->mlState,
            'started_at' => $dto->startedAt->toIso8601String(),
            'last_run_at' => $dto->lastRunAt?->toIso8601String(),
        ];
    }
}

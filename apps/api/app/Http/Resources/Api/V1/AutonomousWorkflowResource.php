<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Autonomous\Data\AutonomousWorkflowDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read AutonomousWorkflowDto $resource */
final class AutonomousWorkflowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'workspace_id' => $dto->workspaceId,
            'status' => $dto->status->value,
            'mode' => $dto->mode->value,
            'correlation_key' => $dto->correlationKey,
            'current_cycle' => $dto->currentCycle,
            'optimization_loop_id' => $dto->optimizationLoopId,
            'workflow_run_id' => $dto->workflowRunId,
            'config' => $dto->config,
            'ml_state' => $dto->mlState,
            'metadata' => $dto->metadata,
            'manual_override_enabled' => $dto->manualOverrideEnabled,
            'autonomous_execution_enabled' => $dto->autonomousExecutionEnabled,
            'locked_at' => $dto->lockedAt?->toIso8601String(),
            'started_at' => $dto->startedAt->toIso8601String(),
            'last_run_at' => $dto->lastRunAt?->toIso8601String(),
        ];
    }
}

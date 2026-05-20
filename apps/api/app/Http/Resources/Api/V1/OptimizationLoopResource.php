<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Optimization\Data\OptimizationLoopDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read OptimizationLoopDto $resource
 */
final class OptimizationLoopResource extends JsonResource
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
            'loop_type' => $dto->loopType->value,
            'status' => $dto->status->value,
            'correlation_key' => $dto->correlationKey,
            'current_cycle' => $dto->currentCycle,
            'config' => $dto->config,
            'ml_state' => $dto->mlState,
            'metadata' => $dto->metadata,
            'started_at' => $dto->startedAt->toIso8601String(),
            'last_run_at' => $dto->lastRunAt?->toIso8601String(),
            'completed_at' => $dto->completedAt?->toIso8601String(),
        ];
    }
}

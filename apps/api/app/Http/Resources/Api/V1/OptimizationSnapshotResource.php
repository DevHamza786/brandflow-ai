<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Optimization\Data\OptimizationSnapshotDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read OptimizationSnapshotDto $resource
 */
final class OptimizationSnapshotResource extends JsonResource
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
            'optimization_loop_id' => $dto->optimizationLoopId,
            'cycle_number' => $dto->cycleNumber,
            'status' => $dto->status->value,
            'engine' => $dto->engine,
            'focus' => $dto->focus,
            'score' => $dto->score,
            'confidence' => $dto->confidence,
            'title' => $dto->title,
            'summary' => $dto->summary,
            'rationale' => $dto->rationale,
            'baseline_metrics' => $dto->baselineMetrics,
            'observed_metrics' => $dto->observedMetrics,
            'delta_metrics' => $dto->deltaMetrics,
            'evidence' => $dto->evidence,
            'action_payload' => $dto->actionPayload,
            'personalization_context' => $dto->personalizationContext,
            'ml_features' => $dto->mlFeatures,
            'captured_at' => $dto->capturedAt->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Experimentation\Data\ExperimentDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read ExperimentDto $resource */
final class ExperimentResource extends JsonResource
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
            'slug' => $dto->slug,
            'name' => $dto->name,
            'experiment_type' => $dto->experimentType->value,
            'status' => $dto->status->value,
            'hypothesis' => $dto->hypothesis,
            'config' => $dto->config,
            'optimization_loop_id' => $dto->optimizationLoopId,
            'started_at' => $dto->startedAt?->toIso8601String(),
        ];
    }
}

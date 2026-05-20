<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Recommendations\Data\RecommendationDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read RecommendationDto $resource
 */
final class RecommendationResource extends JsonResource
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
            'type' => $dto->type->value,
            'status' => $dto->status->value,
            'source' => $dto->source->value,
            'correlation_key' => $dto->correlationKey,
            'title' => $dto->title,
            'summary' => $dto->summary,
            'rationale' => $dto->rationale,
            'score' => $dto->score,
            'confidence' => $dto->confidence,
            'evidence' => $dto->evidence,
            'personalization_context' => $dto->personalizationContext,
            'action_payload' => $dto->actionPayload,
            'ml_state' => $dto->mlState,
            'generated_at' => $dto->generatedAt->toIso8601String(),
            'valid_from' => $dto->validFrom?->toIso8601String(),
            'valid_until' => $dto->validUntil?->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Recommendations\Data\RecommendationDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read RecommendationDto $resource
 */
final class RecommendationSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'type' => $dto->type->value,
            'title' => $dto->title,
            'summary' => $dto->summary,
            'score' => $dto->score,
            'confidence' => $dto->confidence,
            'generated_at' => $dto->generatedAt->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Intelligence\Data\CompetitorDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read CompetitorDto $resource
 */
final class CompetitorResource extends JsonResource
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
            'linkedin_url' => $dto->linkedinUrl,
            'name' => $dto->name,
            'linkedin_urn' => $dto->linkedinUrn,
            'labels' => $dto->labels,
            'metadata' => $dto->metadata,
            'scrape_cadence_hours' => $dto->scrapeCadenceHours,
            'last_scraped_at' => $dto->lastScrapedAt?->toIso8601String(),
            'last_analyzed_at' => $dto->lastAnalyzedAt?->toIso8601String(),
            'intelligence_score' => $dto->intelligenceScore,
            'is_active' => $dto->isActive,
        ];
    }
}

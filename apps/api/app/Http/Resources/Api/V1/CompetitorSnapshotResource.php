<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Intelligence\Data\CompetitorSnapshotDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read CompetitorSnapshotDto $resource
 */
final class CompetitorSnapshotResource extends JsonResource
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
            'competitor_id' => $dto->competitorId,
            'captured_at' => $dto->capturedAt->toIso8601String(),
            'content_hash' => $dto->contentHash,
            'posts_count' => $dto->postsCount,
            'avg_engagement_rate' => $dto->avgEngagementRate,
            'posts_per_week' => $dto->postsPerWeek,
            'intelligence_score' => $dto->intelligenceScore,
            'engagement_metrics' => $dto->engagementMetrics,
            'hook_patterns' => $dto->hookPatterns,
            'posting_cadence' => $dto->postingCadence,
            'content_structure' => $dto->contentStructure,
            'cta_patterns' => $dto->ctaPatterns,
            'trend_summary' => $dto->trendSummary,
            'ml_features' => $dto->mlFeatures,
            'metadata' => $dto->metadata,
        ];
    }
}

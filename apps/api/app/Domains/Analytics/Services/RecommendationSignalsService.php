<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Analytics\Data\RecommendationSignalsDto;

/**
 * Compact signals for future recommendation / RL loops (no training here).
 */
final class RecommendationSignalsService
{
    public function fromSnapshot(PostPerformanceSnapshotDto $snapshot): RecommendationSignalsDto
    {
        $hookScore = is_array($snapshot->hookPerformance) && isset($snapshot->hookPerformance['engine_score'])
            ? (float) $snapshot->hookPerformance['engine_score']
            : null;

        return new RecommendationSignalsDto(
            workspaceId: $snapshot->workspaceId,
            entityType: $snapshot->entityType,
            entityId: $snapshot->entityId,
            normalizedEngagement: (float) ($snapshot->normalizedEngagement ?? 0.0),
            hookPerformanceScore: $hookScore,
            signals: [
                'engagement_rate' => $snapshot->engagementRate,
                'impressions' => $snapshot->impressions,
                'variant_hint' => $snapshot->metadata['variant_key'] ?? null,
            ],
        );
    }
}

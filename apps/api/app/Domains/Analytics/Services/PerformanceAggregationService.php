<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Contracts\FeatureVectorBuilderContract;
use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Analytics\Data\CreatePostPerformanceSnapshotDto;
use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;

/**
 * Computes engagement + hook blend, persists snapshot, stamps ML feature stub.
 */
final class PerformanceAggregationService
{
    public function __construct(
        private readonly PostPerformanceSnapshotRepositoryContract $snapshots,
        private readonly EngagementNormalizationService $normalization,
        private readonly HookPerformanceScoringEngine $hookScores,
        private readonly FeatureVectorBuilderContract $featureBuilder,
    ) {
    }

    public function persistSnapshot(CreatePostPerformanceSnapshotDto $dto): PostPerformanceSnapshotDto
    {
        $raw = $this->normalization->engagementRate(
            $dto->impressions,
            $dto->likes,
            $dto->comments,
            $dto->reposts,
            $dto->saves,
        );
        $norm = $this->normalization->normalize($raw);

        $hookPerf = $dto->hookPerformance;
        $engine = $this->hookScores->score($hookPerf, $norm);
        if ($hookPerf !== null && $engine !== null) {
            $hookPerf['engine_score'] = $engine;
        }

        $stubVector = [
            'impressions' => $dto->impressions,
            'likes' => $dto->likes,
            'comments' => $dto->comments,
            'reposts' => $dto->reposts,
            'saves' => $dto->saves,
            'engagement_rate' => round($raw, 8),
            'normalized_engagement' => round($norm, 8),
            'hook_overall' => is_array($hookPerf) && isset($hookPerf['overall']) ? (float) $hookPerf['overall'] : 0.0,
        ];

        return $this->snapshots->create(new CreatePostPerformanceSnapshotDto(
            workspaceId: $dto->workspaceId,
            entityType: $dto->entityType,
            entityId: $dto->entityId,
            observedAt: $dto->observedAt,
            impressions: $dto->impressions,
            likes: $dto->likes,
            comments: $dto->comments,
            reposts: $dto->reposts,
            saves: $dto->saves,
            providerPostId: $dto->providerPostId,
            postedAt: $dto->postedAt,
            hookPerformance: $hookPerf,
            contentFeatures: $dto->contentFeatures,
            mlFeatures: array_merge($dto->mlFeatures ?? [], ['vector_stub' => $stubVector]),
            metadata: $dto->metadata,
            engagementRate: round($raw, 8),
            normalizedEngagement: round($norm, 8),
        ));
    }
}

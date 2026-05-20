<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Contracts\EngagementMetricRepositoryContract;
use App\Domains\Analytics\Data\CreateEngagementMetricDto;
use App\Domains\Analytics\Data\CreatePostPerformanceSnapshotDto;
use App\Domains\Analytics\Enums\AnalyticsEventType;
use Carbon\CarbonInterface;

/**
 * Records engagement observations → snapshots + daily metric rows + ingestion event.
 */
final class EngagementTrackingService
{
    public function __construct(
        private readonly PerformanceAggregationService $aggregation,
        private readonly EngagementMetricRepositoryContract $metrics,
        private readonly AnalyticsEventIngestionService $ingestion,
        private readonly AnalyticsExecutionLogger $logger,
    ) {
    }

    public function recordPostEngagement(
        string $workspaceId,
        string $entityType,
        string $entityId,
        int $impressions,
        int $likes,
        int $comments,
        int $reposts = 0,
        int $saves = 0,
        ?string $providerPostId = null,
        ?CarbonInterface $postedAt = null,
        ?array $hookPerformance = null,
        ?array $contentFeatures = null,
        ?string $idempotencyKey = null,
    ): void {
        $observedAt = now();

        $snapshot = $this->aggregation->persistSnapshot(new CreatePostPerformanceSnapshotDto(
            workspaceId: $workspaceId,
            entityType: $entityType,
            entityId: $entityId,
            observedAt: $observedAt,
            impressions: $impressions,
            likes: $likes,
            comments: $comments,
            reposts: $reposts,
            saves: $saves,
            providerPostId: $providerPostId,
            postedAt: $postedAt,
            hookPerformance: $hookPerformance,
            contentFeatures: $contentFeatures,
        ));

        $metricDate = $observedAt->toDateString();

        foreach ([
            'impressions' => $impressions,
            'likes' => $likes,
            'comments' => $comments,
            'reposts' => $reposts,
            'saves' => $saves,
            'engagement_rate' => (string) ($snapshot->engagementRate ?? 0),
        ] as $type => $value) {
            $this->metrics->upsertDaily(new CreateEngagementMetricDto(
                workspaceId: $workspaceId,
                measurableType: $entityType,
                measurableId: $entityId,
                metricDate: $metricDate,
                metricType: $type,
                value: (string) $value,
            ));
        }

        $this->ingestion->ingest(
            workspaceId: $workspaceId,
            eventType: AnalyticsEventType::PostPerformanceObserved->value,
            entityType: $entityType,
            entityId: $entityId,
            properties: [
                'snapshot_id' => $snapshot->id,
                'impressions' => $impressions,
                'likes' => $likes,
                'comments' => $comments,
                'normalized_engagement' => $snapshot->normalizedEngagement,
            ],
            idempotencyKey: $idempotencyKey,
        );

        $this->logger->info('engagement.recorded', [
            'workspace_id' => $workspaceId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'snapshot_id' => $snapshot->id,
        ]);
    }
}

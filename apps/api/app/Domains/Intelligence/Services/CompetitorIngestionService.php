<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\CompetitorRepositoryContract;
use App\Domains\Intelligence\Contracts\CompetitorSnapshotRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorSnapshotDto;
use App\Domains\Intelligence\Data\IngestCompetitorSnapshotDto;
use App\Domains\Intelligence\Events\CompetitorSnapshotCaptured;
use App\Domains\Intelligence\Support\CompetitorPayloadNormalizer;
use Illuminate\Support\Facades\Event;

/**
 * Idempotent snapshot ingest (manual / API simulate — no scrape).
 */
final class CompetitorIngestionService
{
    public function __construct(
        private readonly CompetitorRepositoryContract $competitors,
        private readonly CompetitorSnapshotRepositoryContract $snapshots,
        private readonly CompetitorPayloadNormalizer $normalizer,
        private readonly CompetitorAnalyticsService $analytics,
        private readonly CompetitorExecutionLogger $logger,
    ) {
    }

    public function ingest(IngestCompetitorSnapshotDto $dto): CompetitorSnapshotDto
    {
        $competitor = $this->competitors->findById($dto->workspaceId, $dto->competitorId);
        if ($competitor === null) {
            throw new \InvalidArgumentException('Competitor not found.');
        }

        $hash = $this->normalizer->canonicalHash($dto->payload);
        if ($this->snapshots->existsByContentHash($dto->workspaceId, $dto->competitorId, $hash)) {
            $existing = $this->snapshots->findLatestByCompetitor($dto->workspaceId, $dto->competitorId);
            if ($existing !== null) {
                $this->logger->info('ingest_skipped_unchanged', [
                    'workspace_id' => $dto->workspaceId,
                    'competitor_id' => $dto->competitorId,
                ]);

                return $existing;
            }
        }

        $capturedAt = $dto->capturedAt ?? now();

        $snapshot = $this->snapshots->createFromNormalized([
            'workspace_id' => $dto->workspaceId,
            'competitor_id' => $dto->competitorId,
            'captured_at' => $capturedAt,
            'payload' => $dto->payload,
            'content_hash' => $hash,
            'metadata' => array_merge($dto->metadata, ['source' => $dto->source->value]),
        ]);

        $analyzed = $this->analytics->analyzeSnapshot($dto->workspaceId, $snapshot->id);

        Event::dispatch(new CompetitorSnapshotCaptured(
            workspaceId: $dto->workspaceId,
            competitorId: $dto->competitorId,
            snapshotId: $analyzed->id,
        ));

        $this->logger->info('ingest_complete', [
            'workspace_id' => $dto->workspaceId,
            'snapshot_id' => $analyzed->id,
        ]);

        return $analyzed;
    }
}

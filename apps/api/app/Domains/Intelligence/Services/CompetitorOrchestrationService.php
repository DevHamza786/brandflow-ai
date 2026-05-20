<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\CompetitorRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorSnapshotDto;
use App\Domains\Intelligence\Data\IngestCompetitorSnapshotDto;

/**
 * Workflow-friendly entry: ingest → analyze → recommendation sync.
 */
final class CompetitorOrchestrationService
{
    public function __construct(
        private readonly CompetitorIngestionService $ingestion,
        private readonly CompetitorRepositoryContract $competitors,
        private readonly CompetitorRecommendationBridge $recommendationBridge,
        private readonly CompetitorExecutionLogger $logger,
    ) {
    }

    public function ingestAndAnalyze(IngestCompetitorSnapshotDto $dto): CompetitorSnapshotDto
    {
        $snapshot = $this->ingestion->ingest($dto);
        $competitor = $this->competitors->findById($dto->workspaceId, $dto->competitorId);
        if ($competitor !== null) {
            $recs = $this->recommendationBridge->syncForCompetitor(
                $dto->workspaceId,
                $dto->competitorId,
                $competitor,
            );
            $this->logger->info('recommendations_synced', [
                'workspace_id' => $dto->workspaceId,
                'competitor_id' => $dto->competitorId,
                'count' => $recs,
            ]);
        }

        return $snapshot;
    }
}

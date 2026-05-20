<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Actions;

use App\Domains\Intelligence\Data\CompetitorSnapshotDto;
use App\Domains\Intelligence\Data\IngestCompetitorSnapshotDto;
use App\Domains\Intelligence\Services\CompetitorOrchestrationService;

final class IngestCompetitorSnapshotAction
{
    public function __construct(
        private readonly CompetitorOrchestrationService $orchestration,
    ) {
    }

    public function execute(IngestCompetitorSnapshotDto $dto): CompetitorSnapshotDto
    {
        return $this->orchestration->ingestAndAnalyze($dto);
    }
}

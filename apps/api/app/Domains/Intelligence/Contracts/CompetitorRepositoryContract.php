<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

use App\Domains\Intelligence\Data\CompetitorDto;
use App\Domains\Intelligence\Data\CreateCompetitorDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface CompetitorRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function create(CreateCompetitorDto $dto): CompetitorDto;

    public function findById(string $workspaceId, string $id): ?CompetitorDto;

    /**
     * @return list<CompetitorDto>
     */
    public function listActive(string $workspaceId, int $limit = 50): array;

    public function updateIntelligenceScore(
        string $workspaceId,
        string $competitorId,
        float $score,
    ): void;
}

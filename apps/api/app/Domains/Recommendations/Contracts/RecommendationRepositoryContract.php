<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Contracts;

use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationDto;
use App\Domains\Recommendations\Enums\RecommendationStatus;
use App\Domains\Recommendations\Enums\RecommendationType;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface RecommendationRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function create(CreateRecommendationDto $dto): RecommendationDto;

    public function findById(string $workspaceId, string $id): ?RecommendationDto;

    /**
     * @return list<RecommendationDto>
     */
    public function listActive(
        string $workspaceId,
        ?RecommendationType $type = null,
        int $limit = 50,
        int $minScore = 0,
    ): array;

    public function supersedeActiveByCorrelationKey(string $workspaceId, string $correlationKey): int;

    public function countActive(string $workspaceId): int;
}

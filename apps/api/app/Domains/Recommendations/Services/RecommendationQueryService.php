<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Recommendations\Contracts\RecommendationRepositoryContract;
use App\Domains\Recommendations\Data\RecommendationDto;
use App\Domains\Recommendations\Enums\RecommendationType;

final class RecommendationQueryService
{
    public function __construct(
        private readonly RecommendationRepositoryContract $repository,
    ) {
    }

    /**
     * @return list<RecommendationDto>
     */
    public function listActive(
        string $workspaceId,
        ?RecommendationType $type = null,
        int $limit = 50,
    ): array {
        return $this->repository->listActive($workspaceId, $type, $limit);
    }

    public function find(string $workspaceId, string $id): ?RecommendationDto
    {
        return $this->repository->findById($workspaceId, $id);
    }
}

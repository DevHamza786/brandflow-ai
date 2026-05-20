<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use App\Domains\Experimentation\Contracts\ExperimentRepositoryContract;
use App\Domains\Experimentation\Contracts\ExperimentVariantRepositoryContract;
use App\Domains\Experimentation\Data\ExperimentDto;
use App\Domains\Experimentation\Data\ExperimentVariantDto;

final class ExperimentQueryService
{
    public function __construct(
        private readonly ExperimentRepositoryContract $experiments,
        private readonly ExperimentVariantRepositoryContract $variants,
    ) {
    }

    /**
     * @return list<ExperimentDto>
     */
    public function listExperiments(string $workspaceId): array
    {
        return $this->experiments->listRunning($workspaceId, 50);
    }

    public function findExperiment(string $workspaceId, string $id): ?ExperimentDto
    {
        return $this->experiments->findById($workspaceId, $id);
    }

    /**
     * @return list<ExperimentVariantDto>
     */
    public function listVariants(string $workspaceId, string $experimentId): array
    {
        return $this->variants->listByExperiment($workspaceId, $experimentId);
    }
}

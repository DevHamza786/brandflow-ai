<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Contracts;

use App\Domains\Experimentation\Data\CreateExperimentResultDto;
use App\Domains\Experimentation\Data\ExperimentResultDto;
use App\Domains\Experimentation\Enums\ExperimentResultType;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface ExperimentResultRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function create(CreateExperimentResultDto $dto): ExperimentResultDto;

    public function findAssignment(
        string $workspaceId,
        string $experimentId,
        string $subjectKey,
    ): ?ExperimentResultDto;

    /**
     * @return list<ExperimentResultDto>
     */
    public function listObservationsByVariant(string $workspaceId, string $variantId): array;

    public function findByIdempotencyKey(string $workspaceId, string $key): ?ExperimentResultDto;
}

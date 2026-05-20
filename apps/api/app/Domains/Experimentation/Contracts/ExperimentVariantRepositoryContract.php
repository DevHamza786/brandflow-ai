<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Contracts;

use App\Domains\Experimentation\Data\ExperimentVariantDto;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface ExperimentVariantRepositoryContract extends WorkspaceScopedRepositoryContract
{
    /**
     * @return list<ExperimentVariantDto>
     */
    public function ensureTemplateVariants(string $workspaceId, string $experimentId, ExperimentType $type): array;

    /**
     * @return list<ExperimentVariantDto>
     */
    public function listByExperiment(string $workspaceId, string $experimentId): array;

    public function incrementAssignmentCount(string $workspaceId, string $variantId): void;
}

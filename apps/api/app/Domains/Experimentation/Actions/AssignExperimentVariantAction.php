<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Actions;

use App\Domains\Experimentation\Data\VariantAssignmentDto;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Experimentation\Services\ExperimentationEngine;

final class AssignExperimentVariantAction
{
    public function __construct(
        private readonly ExperimentationEngine $engine,
    ) {
    }

    public function execute(
        string $workspaceId,
        ExperimentType $type,
        string $subjectKey,
    ): VariantAssignmentDto {
        return $this->engine->assignVariant($workspaceId, $type, $subjectKey);
    }
}

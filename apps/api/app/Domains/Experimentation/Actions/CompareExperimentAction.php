<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Actions;

use App\Domains\Experimentation\Data\StatisticalComparisonDto;
use App\Domains\Experimentation\Services\ExperimentationEngine;

final class CompareExperimentAction
{
    public function __construct(
        private readonly ExperimentationEngine $engine,
    ) {
    }

    public function execute(string $workspaceId, string $experimentId): StatisticalComparisonDto
    {
        return $this->engine->compareExperiment($workspaceId, $experimentId);
    }
}

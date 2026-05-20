<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Events;

use App\Domains\Experimentation\Data\StatisticalComparisonDto;
use Illuminate\Foundation\Events\Dispatchable;

final class ExperimentComparisonCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly string $workspaceId,
        public readonly string $experimentId,
        public readonly StatisticalComparisonDto $comparison,
    ) {
    }
}

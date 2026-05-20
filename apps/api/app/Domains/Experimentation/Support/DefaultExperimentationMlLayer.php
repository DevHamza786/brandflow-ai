<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Support;

use App\Domains\Experimentation\Contracts\ExperimentationMlCompatibilityLayerContract;

final class DefaultExperimentationMlLayer implements ExperimentationMlCompatibilityLayerContract
{
    public function afterComparison(array $mlState, array $comparison): array
    {
        if (! (bool) config('experimentation.ml.enabled', false)) {
            return $mlState;
        }

        $mlState['last_comparison'] = $comparison;

        return $mlState;
    }
}

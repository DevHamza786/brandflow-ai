<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Support;

use App\Domains\Coordination\Contracts\CoordinationMlCompatibilityLayerContract;

/**
 * Stub for future RL / experiment routing overrides.
 */
final class DefaultCoordinationMlLayer implements CoordinationMlCompatibilityLayerContract
{
    public function afterCycle(array $mlState, array $cycleMetrics): array
    {
        if (! (bool) config('coordination.ml.enabled', false)) {
            return $mlState;
        }

        $mlState['last_cycle_metrics'] = $cycleMetrics;
        $mlState['experiment_bucket'] = config('coordination.ml.experiment_bucket');

        return $mlState;
    }

    public function enrichRouting(array $routingPayload): array
    {
        return $routingPayload;
    }
}

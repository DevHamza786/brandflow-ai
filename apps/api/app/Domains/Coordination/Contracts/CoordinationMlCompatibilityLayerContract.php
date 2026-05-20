<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Contracts;

/**
 * Future RL / experiment-engine hook — no-op in v1.
 */
interface CoordinationMlCompatibilityLayerContract
{
    /**
     * @param  array<string, mixed>  $mlState
     * @param  array<string, mixed>  $cycleMetrics
     * @return array<string, mixed>
     */
    public function afterCycle(array $mlState, array $cycleMetrics): array;

    /**
     * @param  array<string, mixed>  $routingPayload
     * @return array<string, mixed>
     */
    public function enrichRouting(array $routingPayload): array;
}

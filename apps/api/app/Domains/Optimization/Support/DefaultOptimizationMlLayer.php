<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Support;

use App\Domains\Optimization\Contracts\OptimizationMlCompatibilityLayerContract;
use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;

/**
 * Portable ML / RL / embedding hooks without training logic.
 */
final class DefaultOptimizationMlLayer implements OptimizationMlCompatibilityLayerContract
{
    public function enrichFeatures(CreateOptimizationSnapshotDto $draft): array
    {
        return array_merge($draft->mlFeatures, [
            'schema_version' => 1,
            'feature_vector_ref' => null,
            'embedding_ref' => null,
            'bandit_arm' => $draft->focus,
            'rl_policy_id' => null,
            'experiment_id' => null,
            'reward_signal' => $draft->deltaMetrics['uplift_pct'] ?? null,
            'engine' => $draft->engine,
            'cycle_number' => $draft->cycleNumber,
        ]);
    }
}

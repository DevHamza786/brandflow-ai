<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Support;

use App\Domains\Recommendations\Contracts\MlCompatibilityLayerContract;
use App\Domains\Recommendations\Data\CreateRecommendationDto;

/**
 * Portable ML / RL / embedding hooks without training logic.
 */
final class DefaultMlCompatibilityLayer implements MlCompatibilityLayerContract
{
    public function enrichState(CreateRecommendationDto $draft): array
    {
        return array_merge($draft->mlState, [
            'schema_version' => 1,
            'feature_vector_ref' => null,
            'embedding_ref' => null,
            'bandit_arm' => null,
            'rl_policy_id' => null,
            'experiment_id' => null,
            'reward_signal' => $draft->evidence['uplift_pct'] ?? null,
            'correlation_key' => $draft->correlationKey,
            'type' => $draft->type->value,
        ]);
    }
}

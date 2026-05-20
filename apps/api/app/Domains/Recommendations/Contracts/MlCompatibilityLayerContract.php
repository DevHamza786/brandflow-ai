<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Contracts;

use App\Domains\Recommendations\Data\CreateRecommendationDto;

/**
 * Future embeddings, bandits, RL — stamps portable `ml_state` on recommendations.
 */
interface MlCompatibilityLayerContract
{
    /**
     * @return array<string, mixed>
     */
    public function enrichState(CreateRecommendationDto $draft): array;
}

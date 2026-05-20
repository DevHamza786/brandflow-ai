<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use App\Domains\Experimentation\Data\StatisticalComparisonDto;
use App\Domains\Recommendations\Contracts\RecommendationRepositoryContract;

/**
 * Surfaces experiment winners as recommendation validation signals (read/sync stub).
 */
final class ExperimentRecommendationIntegration
{
    public function __construct(
        private readonly RecommendationRepositoryContract $recommendations,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function validationContext(string $workspaceId): array
    {
        $recent = $this->recommendations->listActive($workspaceId, 5);

        return [
            'active_recommendation_count' => count($recent),
            'ref_type' => 'recommendation_validation',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function comparisonPayload(StatisticalComparisonDto $comparison): array
    {
        return [
            'winner' => $comparison->winnerVariantKey,
            'lift_percent' => $comparison->liftPercent,
            'confidence' => $comparison->confidence,
            'is_significant' => $comparison->isSignificant,
            'narrative' => $comparison->narrative,
        ];
    }
}

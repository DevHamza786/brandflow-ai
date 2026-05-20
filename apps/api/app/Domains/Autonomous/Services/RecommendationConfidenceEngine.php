<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\Recommendations\Data\RecommendationDto;

/**
 * Threshold gate — blocks reckless automation on weak recommendations.
 */
final class RecommendationConfidenceEngine
{
    public function passes(RecommendationDto $recommendation, float $minConfidence, int $minScore): bool
    {
        if ($recommendation->score < $minScore) {
            return false;
        }

        $confidence = $recommendation->confidence ?? $this->inferConfidence($recommendation);

        return $confidence >= $minConfidence;
    }

    public function inferConfidence(RecommendationDto $recommendation): float
    {
        if ($recommendation->confidence !== null) {
            return (float) $recommendation->confidence;
        }

        $sample = (int) ($recommendation->evidence['sample_size'] ?? 0);
        $uplift = isset($recommendation->evidence['uplift_pct'])
            ? abs((float) $recommendation->evidence['uplift_pct'])
            : 0.0;

        return min(0.99, 0.25 + min(0.4, $sample / 20) + min(0.35, $uplift / 80));
    }

    public function combinedConfidence(float ...$values): float
    {
        $filtered = array_values(array_filter($values, static fn ($v) => $v > 0));
        if ($filtered === []) {
            return 0.0;
        }

        return round(array_sum($filtered) / count($filtered), 4);
    }
}

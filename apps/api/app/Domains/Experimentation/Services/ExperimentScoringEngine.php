<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use App\Domains\Experimentation\Data\ExperimentVariantDto;

/**
 * Aggregates observation metrics per variant arm.
 */
final class ExperimentScoringEngine
{
    /**
     * @param  list<array{impressions: int, engagements: int, normalized_score: float}>  $observations
     * @return array{samples: int, engagement_rate: float, avg_normalized: float}
     */
    public function aggregate(array $observations): array
    {
        $samples = count($observations);
        if ($samples === 0) {
            return ['samples' => 0, 'engagement_rate' => 0.0, 'avg_normalized' => 0.0];
        }

        $impressions = 0;
        $engagements = 0;
        $normalizedSum = 0.0;

        foreach ($observations as $row) {
            $impressions += (int) ($row['impressions'] ?? 0);
            $engagements += (int) ($row['engagements'] ?? 0);
            $normalizedSum += (float) ($row['normalized_score'] ?? 0);
        }

        $engagementRate = $impressions > 0 ? $engagements / $impressions : 0.0;

        return [
            'samples' => $samples,
            'engagement_rate' => round($engagementRate, 6),
            'avg_normalized' => round($normalizedSum / $samples, 4),
        ];
    }

    /**
     * @param  array{samples: int, engagement_rate: float, avg_normalized: float}  $control
     * @param  array{samples: int, engagement_rate: float, avg_normalized: float}  $variant
     */
    public function scoreLift(
        array $control,
        array $variant,
        ExperimentVariantDto $controlVariant,
        ExperimentVariantDto $challengerVariant,
    ): float {
        if ($control['engagement_rate'] <= 0) {
            return $variant['engagement_rate'] > 0 ? 100.0 : 0.0;
        }

        return (($variant['engagement_rate'] - $control['engagement_rate']) / $control['engagement_rate']) * 100;
    }
}

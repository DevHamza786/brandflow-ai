<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use App\Domains\Experimentation\Data\ExperimentVariantDto;
use App\Domains\Experimentation\Data\StatisticalComparisonDto;

/**
 * Two-proportion z-test style comparison for experiment arms.
 */
final class StatisticalComparisonEngine
{
    public function __construct(
        private readonly ExperimentScoringEngine $scoring,
    ) {
    }

    /**
     * @param  list<array{impressions: int, engagements: int, normalized_score: float}>  $controlObs
     * @param  list<array{impressions: int, engagements: int, normalized_score: float}>  $variantObs
     */
    public function compare(
        string $experimentId,
        ExperimentVariantDto $control,
        ExperimentVariantDto $challenger,
        array $controlObs,
        array $variantObs,
    ): StatisticalComparisonDto {
        $controlAgg = $this->scoring->aggregate($controlObs);
        $variantAgg = $this->scoring->aggregate($variantObs);

        $minSamples = (int) config('experimentation.min_samples_for_comparison', 30);
        $confidenceThreshold = (float) config('experimentation.confidence_threshold', 0.90);

        $lift = $this->scoring->scoreLift($controlAgg, $variantAgg, $control, $challenger);
        $confidence = $this->estimateConfidence($controlAgg, $variantAgg, $minSamples);
        $isSignificant = $confidence >= $confidenceThreshold
            && $controlAgg['samples'] >= $minSamples
            && $variantAgg['samples'] >= $minSamples;

        $winner = $lift >= 0 ? $challenger : $control;
        $loser = $lift >= 0 ? $control : $challenger;
        $absLift = abs($lift);

        $narrative = sprintf(
            '%s outperforms %s by %.1f%% with %.0f%% confidence.',
            $winner->label ?? $winner->variantKey,
            $loser->label ?? $loser->variantKey,
            $absLift,
            $confidence * 100,
        );

        return new StatisticalComparisonDto(
            experimentId: $experimentId,
            winnerVariantKey: $winner->variantKey,
            loserVariantKey: $loser->variantKey,
            liftPercent: round($absLift, 2),
            confidence: round($confidence, 4),
            isSignificant: $isSignificant,
            narrative: $narrative,
            controlSamples: $controlAgg['samples'],
            variantSamples: $variantAgg['samples'],
        );
    }

    /**
     * @param  array{samples: int, engagement_rate: float}  $control
     * @param  array{samples: int, engagement_rate: float}  $variant
     */
    private function estimateConfidence(array $control, array $variant, int $minSamples): float
    {
        $n1 = max(1, $control['samples']);
        $n2 = max(1, $variant['samples']);
        $p1 = $control['engagement_rate'];
        $p2 = $variant['engagement_rate'];

        $pPool = ($p1 * $n1 + $p2 * $n2) / ($n1 + $n2);
        $se = sqrt($pPool * (1 - $pPool) * ((1 / $n1) + (1 / $n2)));

        if ($se <= 0) {
            return min(1.0, ($n1 + $n2) / ($minSamples * 2));
        }

        $z = abs($p2 - $p1) / $se;
        $confidence = 1 - (2 * (1 - $this->normalCdf($z)));

        return max(0.0, min(0.999, $confidence));
    }

    private function normalCdf(float $z): float
    {
        $t = 1 / (1 + 0.2316419 * abs($z));
        $d = 0.3989423 * exp(-$z * $z / 2);
        $p = $d * $t * (0.3193815 + $t * (-0.3565638 + $t * (1.781478 + $t * (-1.821256 + $t * 1.330274))));

        return $z > 0 ? 1 - $p : $p;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Recommendations\Data\RecommendationContextDto;
use App\Domains\Recommendations\Support\HookStyleClassifier;

/**
 * Style-level uplift vs workspace baseline (evidence-backed patterns).
 */
final class HookStyleCorrelationEngine
{
    public function __construct(
        private readonly HookStyleClassifier $classifier,
    ) {
    }

    /**
     * @return list<array{style:string,label:string,sample_size:int,avg_normalized:float,uplift_pct:float}>
     */
    public function rankedStyles(RecommendationContextDto $context): array
    {
        $baseline = max(0.0001, $context->baselineNormalized);
        $minSamples = (int) config('recommendations.min_samples_style', 3);
        $insights = [];

        foreach ($context->snapshotsByStyle as $style => $snapshots) {
            if ($style === HookStyleClassifier::STYLE_UNKNOWN) {
                continue;
            }
            $norms = $this->normsFromSnapshots($snapshots);
            if (count($norms) < $minSamples) {
                continue;
            }
            $avg = array_sum($norms) / count($norms);
            $uplift = (($avg - $baseline) / $baseline) * 100;
            $insights[] = [
                'style' => $style,
                'label' => $this->classifier->label($style),
                'sample_size' => count($norms),
                'avg_normalized' => round($avg, 6),
                'uplift_pct' => round($uplift, 1),
            ];
        }

        usort($insights, static fn ($a, $b) => $b['uplift_pct'] <=> $a['uplift_pct']);

        return $insights;
    }

    /**
     * @return list<array{style:string,label:string,sample_size:int,avg_normalized:float,uplift_pct:float}>
     */
    public function underperformingStyles(RecommendationContextDto $context): array
    {
        $minUplift = (float) config('recommendations.min_uplift_pct', 12.0);

        return array_values(array_filter(
            $this->rankedStyles($context),
            static fn (array $row) => $row['uplift_pct'] <= -$minUplift,
        ));
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     * @return list<float>
     */
    private function normsFromSnapshots(array $snapshots): array
    {
        $out = [];
        foreach ($snapshots as $s) {
            if ($s->normalizedEngagement !== null) {
                $out[] = (float) $s->normalizedEngagement;
            }
        }

        return $out;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Support;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Recommendations\Support\HookStyleClassifier;

/**
 * Period-over-period deltas for optimization intelligence.
 */
final class HistoricalComparisonSupport
{
    /**
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     */
    public function avgNormalized(array $snapshots): float
    {
        $vals = [];
        foreach ($snapshots as $s) {
            if ($s->normalizedEngagement !== null) {
                $vals[] = (float) $s->normalizedEngagement;
            }
        }

        return $vals !== [] ? array_sum($vals) / count($vals) : 0.0;
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     */
    public function avgEngagementRate(array $snapshots): float
    {
        $vals = [];
        foreach ($snapshots as $s) {
            if ($s->engagementRate !== null) {
                $vals[] = (float) $s->engagementRate;
            }
        }

        return $vals !== [] ? array_sum($vals) / count($vals) : 0.0;
    }

    public function upliftPct(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $current
     * @param  list<PostPerformanceSnapshotDto>  $previous
     * @return list<array{style:string,label:string,current_avg:float,previous_avg:float,uplift_pct:?float,sample_current:int,sample_previous:int}>
     */
    public function hookStyleComparison(
        array $current,
        array $previous,
        HookStyleClassifier $classifier,
    ): array {
        $curBuckets = $this->styleBuckets($current, $classifier);
        $prevBuckets = $this->styleBuckets($previous, $classifier);
        $styles = array_unique(array_merge(array_keys($curBuckets), array_keys($prevBuckets)));
        $out = [];

        foreach ($styles as $style) {
            if ($style === HookStyleClassifier::STYLE_UNKNOWN) {
                continue;
            }
            $cur = $curBuckets[$style] ?? ['sum' => 0.0, 'n' => 0];
            $prev = $prevBuckets[$style] ?? ['sum' => 0.0, 'n' => 0];
            $curAvg = $cur['n'] > 0 ? $cur['sum'] / $cur['n'] : 0.0;
            $prevAvg = $prev['n'] > 0 ? $prev['sum'] / $prev['n'] : 0.0;
            $out[] = [
                'style' => $style,
                'label' => $classifier->label($style),
                'current_avg' => round($curAvg, 6),
                'previous_avg' => round($prevAvg, 6),
                'uplift_pct' => $this->upliftPct($curAvg, $prevAvg),
                'sample_current' => $cur['n'],
                'sample_previous' => $prev['n'],
            ];
        }

        usort($out, static fn ($a, $b) => ($b['uplift_pct'] ?? 0) <=> ($a['uplift_pct'] ?? 0));

        return $out;
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     * @return array<string, array{sum:float,n:int}>
     */
    private function styleBuckets(array $snapshots, HookStyleClassifier $classifier): array
    {
        $buckets = [];
        foreach ($snapshots as $s) {
            $text = is_array($s->hookPerformance) && isset($s->hookPerformance['text'])
                ? (string) $s->hookPerformance['text']
                : '';
            $style = $classifier->classify($text);
            $norm = (float) ($s->normalizedEngagement ?? 0);
            $buckets[$style] ??= ['sum' => 0.0, 'n' => 0];
            $buckets[$style]['sum'] += $norm;
            $buckets[$style]['n']++;
        }

        return $buckets;
    }
}

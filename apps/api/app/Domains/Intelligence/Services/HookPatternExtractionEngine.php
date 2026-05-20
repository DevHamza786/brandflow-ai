<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Recommendations\Support\HookStyleClassifier;

/**
 * Competitor hook-style distribution + uplift vs snapshot baseline.
 */
final class HookPatternExtractionEngine
{
    public function __construct(
        private readonly HookStyleClassifier $classifier,
    ) {
    }

    /**
     * @param  list<array<string, mixed>>  $posts
     * @return array<string, mixed>
     */
    public function extract(array $posts): array
    {
        $buckets = [];
        $rates = [];

        foreach ($posts as $post) {
            $style = $this->classifier->classify((string) ($post['hook_text'] ?? ''));
            if ($style === HookStyleClassifier::STYLE_UNKNOWN) {
                continue;
            }
            $impressions = (int) ($post['impressions'] ?? 0);
            $likes = (int) ($post['likes'] ?? 0);
            $comments = (int) ($post['comments'] ?? 0);
            $reposts = (int) ($post['reposts'] ?? 0);
            $saves = (int) ($post['saves'] ?? 0);
            $rate = $impressions > 0
                ? ($likes + $comments + $reposts + $saves) / $impressions
                : 0.0;

            $buckets[$style] ??= ['count' => 0, 'rate_sum' => 0.0];
            $buckets[$style]['count']++;
            $buckets[$style]['rate_sum'] += $rate;
            $rates[] = $rate;
        }

        $baseline = $rates !== [] ? array_sum($rates) / count($rates) : 0.0;
        $styles = [];
        foreach ($buckets as $style => $agg) {
            $avg = $agg['count'] > 0 ? $agg['rate_sum'] / $agg['count'] : 0.0;
            $uplift = $baseline > 0 ? (($avg - $baseline) / $baseline) * 100 : 0.0;
            $styles[] = [
                'style' => $style,
                'label' => $this->classifier->label($style),
                'sample_count' => $agg['count'],
                'avg_engagement_rate' => round($avg, 6),
                'uplift_pct_vs_snapshot' => round($uplift, 1),
            ];
        }

        usort($styles, static fn ($a, $b) => $b['uplift_pct_vs_snapshot'] <=> $a['uplift_pct_vs_snapshot']);

        return [
            'baseline_engagement_rate' => round($baseline, 6),
            'styles' => $styles,
            'dominant_style' => $styles[0]['style'] ?? null,
            'insights' => $this->buildInsights($styles),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $styles
     * @return list<array<string, mixed>>
     */
    private function buildInsights(array $styles): array
    {
        $minSamples = (int) config('intelligence.min_samples_style_uplift', 3);
        $minUplift = (float) config('intelligence.min_style_uplift_pct', 15.0);
        $insights = [];

        if (count($styles) < 2) {
            return $insights;
        }

        $best = $styles[0];
        $worst = $styles[count($styles) - 1];
        if ($best['sample_count'] >= $minSamples && $worst['sample_count'] >= $minSamples) {
            $gap = $best['uplift_pct_vs_snapshot'] - $worst['uplift_pct_vs_snapshot'];
            if ($gap >= $minUplift) {
                $insights[] = [
                    'kind' => 'style_gap',
                    'summary' => sprintf(
                        '%s hooks outperform %s hooks by %.1f%% in this competitor snapshot.',
                        ucfirst($best['label']),
                        $worst['label'],
                        abs($gap),
                    ),
                    'best_style' => $best['style'],
                    'worst_style' => $worst['style'],
                    'gap_pct' => round($gap, 1),
                ];
            }
        }

        return $insights;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Analytics\Services\PostingTimeAnalyzer;
use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Recommendations\Data\RecommendationContextDto;
use App\Domains\Recommendations\Support\HookStyleClassifier;

/**
 * Builds workspace performance context from snapshots (no raw event scans).
 */
final class AnalyticsCorrelationEngine
{
    public function __construct(
        private readonly PostPerformanceSnapshotRepositoryContract $snapshots,
        private readonly PostingTimeAnalyzer $postingTime,
        private readonly BrandProfileRepositoryContract $brandProfiles,
        private readonly HookStyleClassifier $styleClassifier,
    ) {
    }

    public function buildContext(string $workspaceId, ?int $lookbackDays = null): RecommendationContextDto
    {
        $lookbackDays = $lookbackDays ?? (int) config('recommendations.lookback_days', 90);
        $maxSnapshots = (int) config('recommendations.max_snapshots', 500);

        $rows = $this->snapshots->listRecentForWorkspace($workspaceId, $lookbackDays, $maxSnapshots);
        $norms = $this->normalizedValues($rows);
        $baseline = $norms !== [] ? array_sum($norms) / count($norms) : 0.0;
        $rates = array_values(array_filter(array_map(
            static fn (PostPerformanceSnapshotDto $s) => $s->engagementRate,
            $rows,
        )));
        $baselineRate = $rates !== [] ? array_sum($rates) / count($rates) : 0.0;

        sort($norms);
        $p25 = $this->percentile($norms, 25);
        $p75 = $this->percentile($norms, 75);

        $byStyle = [];
        foreach ($rows as $snapshot) {
            $text = is_array($snapshot->hookPerformance) && isset($snapshot->hookPerformance['text'])
                ? (string) $snapshot->hookPerformance['text']
                : null;
            $style = $this->styleClassifier->classify($text);
            $byStyle[$style] ??= [];
            $byStyle[$style][] = $snapshot;
        }

        $profile = $this->brandProfiles->findPrimaryByWorkspace($workspaceId);

        return new RecommendationContextDto(
            workspaceId: $workspaceId,
            lookbackDays: $lookbackDays,
            snapshots: $rows,
            baselineNormalized: round($baseline, 6),
            baselineEngagementRate: round($baselineRate, 6),
            p25Normalized: round($p25, 6),
            p75Normalized: round($p75, 6),
            snapshotsByStyle: $byStyle,
            postingHourHistogram: $this->postingTime->hourlyEngagementProfile($workspaceId, $lookbackDays),
            postsPerWeek: $this->estimatePostsPerWeek($rows, $lookbackDays),
            brandProfile: $profile,
            personalizationBase: $this->personalizationBase($profile, $lookbackDays),
        );
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $rows
     * @return list<float>
     */
    private function normalizedValues(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if ($row->normalizedEngagement !== null) {
                $out[] = (float) $row->normalizedEngagement;
            }
        }

        return $out;
    }

    /**
     * @param  list<float>  $sorted
     */
    private function percentile(array $sorted, int $pct): float
    {
        if ($sorted === []) {
            return 0.0;
        }
        $idx = (int) floor(($pct / 100) * (count($sorted) - 1));

        return $sorted[max(0, $idx)];
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $rows
     */
    private function estimatePostsPerWeek(array $rows, int $lookbackDays): float
    {
        $withPosted = array_filter($rows, static fn (PostPerformanceSnapshotDto $s) => $s->postedAt !== null);
        $weeks = max(1, $lookbackDays / 7);

        return round(count($withPosted) / $weeks, 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function personalizationBase(?\App\Domains\Brand\Data\BrandProfileDto $profile, int $lookbackDays): array
    {
        if ($profile === null) {
            return [
                'brand_profile_id' => null,
                'audience_segments' => [],
                'tone' => null,
                'lookback_days' => $lookbackDays,
            ];
        }

        return [
            'brand_profile_id' => $profile->id,
            'audience_segments' => $profile->targetAudience->segments,
            'audience_summary' => $profile->targetAudience->summary,
            'tone' => $profile->toneProfile->primary ?? $profile->brandVoice,
            'preferred_hook_patterns' => $profile->preferredHookPatterns,
            'preferred_ctas' => $profile->preferredCtas,
            'lookback_days' => $lookbackDays,
        ];
    }
}

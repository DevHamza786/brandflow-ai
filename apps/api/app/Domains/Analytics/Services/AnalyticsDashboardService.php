<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Analytics\Data\AnalyticsDashboardDto;
use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Read-side dashboard aggregation from `post_performance_snapshots` (no raw event scans).
 */
final class AnalyticsDashboardService
{
    public function __construct(
        private readonly PostPerformanceSnapshotRepositoryContract $snapshots,
        private readonly PostingTimeAnalyzer $postingTime,
    ) {
    }

    public function build(
        string $workspaceId,
        CarbonInterface $from,
        CarbonInterface $to,
        ?string $preset = null,
    ): AnalyticsDashboardDto {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        $rows = $this->snapshots->listObservedBetween($workspaceId, $from, $to);

        $daysBack = max(1, (int) $from->diffInDays($to) + 1);
        $postingTime = $this->filterPostingTimeByRange(
            $this->postingTime->hourlyEngagementProfile($workspaceId, $daysBack),
            $from,
            $to,
            $rows,
        );

        $kpis = $this->computeKpis($rows);
        $engagementSeries = $this->buildEngagementSeries($rows, $from, $to);
        $scoreTrend = $this->buildScoreTrend($rows, $from, $to);
        $postingFrequency = $this->buildPostingFrequency($rows, $from, $to);
        $topHooks = $this->buildTopHooks($rows);
        $audience = $this->buildAudienceOverview($rows);
        $comparison = $this->buildComparison($workspaceId, $from, $to, $kpis);

        return new AnalyticsDashboardDto(
            range: [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
                'preset' => $preset,
                'label' => $this->rangeLabel($preset, $from, $to),
            ],
            kpis: $kpis,
            engagementSeries: $engagementSeries,
            scoreTrend: $scoreTrend,
            postingFrequency: $postingFrequency,
            postingTime: $postingTime,
            topHooks: $topHooks,
            audienceOverview: $audience,
            comparison: $comparison,
        );
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $rows
     * @return array<string, int|float|null>
     */
    private function computeKpis(array $rows): array
    {
        if ($rows === []) {
            return [
                'impressions' => 0,
                'likes' => 0,
                'comments' => 0,
                'reposts' => 0,
                'saves' => 0,
                'posts_observed' => 0,
                'engagement_rate_avg' => null,
                'normalized_engagement_avg' => null,
                'hook_score_avg' => null,
            ];
        }

        $impressions = 0;
        $likes = 0;
        $comments = 0;
        $reposts = 0;
        $saves = 0;
        $rates = [];
        $norms = [];
        $hookScores = [];

        foreach ($rows as $r) {
            $impressions += $r->impressions;
            $likes += $r->likes;
            $comments += $r->comments;
            $reposts += $r->reposts;
            $saves += $r->saves;
            if ($r->engagementRate !== null) {
                $rates[] = $r->engagementRate;
            }
            if ($r->normalizedEngagement !== null) {
                $norms[] = $r->normalizedEngagement;
            }
            if (is_array($r->hookPerformance) && isset($r->hookPerformance['engine_score'])) {
                $hookScores[] = (float) $r->hookPerformance['engine_score'];
            }
        }

        return [
            'impressions' => $impressions,
            'likes' => $likes,
            'comments' => $comments,
            'reposts' => $reposts,
            'saves' => $saves,
            'posts_observed' => count($rows),
            'engagement_rate_avg' => $rates !== [] ? round(array_sum($rates) / count($rates), 6) : null,
            'normalized_engagement_avg' => $norms !== [] ? round(array_sum($norms) / count($norms), 6) : null,
            'hook_score_avg' => $hookScores !== [] ? round(array_sum($hookScores) / count($hookScores), 2) : null,
        ];
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $rows
     * @return list<array<string, mixed>>
     */
    private function buildEngagementSeries(array $rows, CarbonInterface $from, CarbonInterface $to): array
    {
        $buckets = $this->emptyDateBuckets($from, $to);

        foreach ($rows as $r) {
            $key = $r->observedAt->format('Y-m-d');
            if (! isset($buckets[$key])) {
                continue;
            }
            $buckets[$key]['impressions'] += $r->impressions;
            $buckets[$key]['likes'] += $r->likes;
            $buckets[$key]['comments'] += $r->comments;
            $buckets[$key]['reposts'] += $r->reposts;
            $buckets[$key]['saves'] += $r->saves;
            $buckets[$key]['posts']++;
            if ($r->engagementRate !== null) {
                $buckets[$key]['rate_sum'] += $r->engagementRate;
                $buckets[$key]['rate_n']++;
            }
        }

        $out = [];
        foreach ($buckets as $date => $b) {
            $out[] = [
                'date' => $date,
                'impressions' => $b['impressions'],
                'likes' => $b['likes'],
                'comments' => $b['comments'],
                'reposts' => $b['reposts'],
                'saves' => $b['saves'],
                'posts' => $b['posts'],
                'engagement_rate' => $b['rate_n'] > 0 ? round($b['rate_sum'] / $b['rate_n'], 6) : null,
            ];
        }

        return $out;
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $rows
     * @return list<array<string, mixed>>
     */
    private function buildScoreTrend(array $rows, CarbonInterface $from, CarbonInterface $to): array
    {
        $buckets = $this->emptyDateBuckets($from, $to);

        foreach ($rows as $r) {
            $key = $r->observedAt->format('Y-m-d');
            if (! isset($buckets[$key])) {
                continue;
            }
            if ($r->normalizedEngagement !== null) {
                $buckets[$key]['norm_sum'] += $r->normalizedEngagement;
                $buckets[$key]['norm_n']++;
            }
            if (is_array($r->hookPerformance) && isset($r->hookPerformance['engine_score'])) {
                $buckets[$key]['hook_sum'] += (float) $r->hookPerformance['engine_score'];
                $buckets[$key]['hook_n']++;
            }
        }

        $out = [];
        foreach ($buckets as $date => $b) {
            $out[] = [
                'date' => $date,
                'avg_normalized' => $b['norm_n'] > 0 ? round($b['norm_sum'] / $b['norm_n'], 4) : null,
                'avg_hook_score' => $b['hook_n'] > 0 ? round($b['hook_sum'] / $b['hook_n'], 2) : null,
            ];
        }

        return $out;
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $rows
     * @return list<array<string, mixed>>
     */
    private function buildPostingFrequency(array $rows, CarbonInterface $from, CarbonInterface $to): array
    {
        $buckets = $this->emptyDateBuckets($from, $to);

        foreach ($rows as $r) {
            $at = $r->postedAt ?? $r->observedAt;
            $key = $at->format('Y-m-d');
            if (isset($buckets[$key])) {
                $buckets[$key]['count']++;
            }
        }

        $out = [];
        foreach ($buckets as $date => $b) {
            $out[] = ['date' => $date, 'posts' => $b['count']];
        }

        return $out;
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $rows
     * @return list<array<string, mixed>>
     */
    private function buildTopHooks(array $rows): array
    {
        $sorted = $rows;
        usort($sorted, static function (PostPerformanceSnapshotDto $a, PostPerformanceSnapshotDto $b): int {
            return ($b->normalizedEngagement ?? 0) <=> ($a->normalizedEngagement ?? 0);
        });

        $top = array_slice($sorted, 0, 10);
        $out = [];
        foreach ($top as $r) {
            $hook = $r->hookPerformance;
            $out[] = [
                'id' => $r->id,
                'entity_id' => $r->entityId,
                'hook_text' => is_array($hook) && isset($hook['text']) ? (string) $hook['text'] : null,
                'normalized' => $r->normalizedEngagement,
                'hook_score' => is_array($hook) && isset($hook['engine_score']) ? (float) $hook['engine_score'] : null,
                'overall_lab_score' => is_array($hook) && isset($hook['overall']) ? (float) $hook['overall'] : null,
                'impressions' => $r->impressions,
                'likes' => $r->likes,
                'comments' => $r->comments,
                'observed_at' => $r->observedAt->toIso8601String(),
            ];
        }

        return $out;
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $rows
     * @return array<string, mixed>
     */
    private function buildAudienceOverview(array $rows): array
    {
        $totalInteractions = 0;
        foreach ($rows as $r) {
            $totalInteractions += $r->likes + $r->comments + $r->reposts + $r->saves;
        }

        return [
            'total_interactions' => $totalInteractions,
            'interaction_mix' => [
                'likes' => array_sum(array_map(static fn ($r) => $r->likes, $rows)),
                'comments' => array_sum(array_map(static fn ($r) => $r->comments, $rows)),
                'reposts' => array_sum(array_map(static fn ($r) => $r->reposts, $rows)),
                'saves' => array_sum(array_map(static fn ($r) => $r->saves, $rows)),
            ],
            'avg_impressions_per_post' => $rows !== []
                ? (int) round(array_sum(array_map(static fn ($r) => $r->impressions, $rows)) / count($rows))
                : 0,
        ];
    }

    /**
     * @param  array<string, int|float|null>  $currentKpis
     * @return array<string, mixed>
     */
    private function buildComparison(string $workspaceId, CarbonInterface $from, CarbonInterface $to, array $currentKpis): array
    {
        $spanDays = max(1, (int) $from->diffInDays($to) + 1);
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($spanDays - 1)->startOfDay();

        $prevRows = $this->snapshots->listObservedBetween($workspaceId, $prevFrom, $prevTo);
        $prevKpis = $this->computeKpis($prevRows);

        return [
            'previous_range' => [
                'from' => $prevFrom->toIso8601String(),
                'to' => $prevTo->toIso8601String(),
            ],
            'engagement_rate_delta' => $this->deltaPct(
                $currentKpis['engagement_rate_avg'],
                $prevKpis['engagement_rate_avg'],
            ),
            'impressions_delta' => $this->deltaPct(
                $currentKpis['impressions'],
                $prevKpis['impressions'],
            ),
            'posts_observed_delta' => $this->deltaPct(
                $currentKpis['posts_observed'],
                $prevKpis['posts_observed'],
            ),
        ];
    }

    /**
     * @param  list<array{hour:int,sample_count:int,avg_normalized:float}>  $histogram
     * @param  list<PostPerformanceSnapshotDto>  $rows
     * @return list<array{hour:int,sample_count:int,avg_normalized:float}>
     */
    private function filterPostingTimeByRange(array $histogram, CarbonInterface $from, CarbonInterface $to, array $rows): array
    {
        if ($rows !== [] && $histogram === []) {
            $buckets = [];
            foreach ($rows as $r) {
                $at = $r->postedAt ?? $r->observedAt;
                $h = (int) $at->format('G');
                if (! isset($buckets[$h])) {
                    $buckets[$h] = ['sum' => 0.0, 'n' => 0];
                }
                $buckets[$h]['sum'] += (float) ($r->normalizedEngagement ?? 0);
                $buckets[$h]['n']++;
            }
            $histogram = [];
            foreach ($buckets as $hour => $agg) {
                $histogram[] = [
                    'hour' => $hour,
                    'sample_count' => $agg['n'],
                    'avg_normalized' => $agg['n'] > 0 ? $agg['sum'] / $agg['n'] : 0.0,
                ];
            }
            usort($histogram, static fn ($a, $b) => $a['hour'] <=> $b['hour']);
        }

        return $histogram;
    }

    /**
     * @return array<string, array<string, float|int>>
     */
    private function emptyDateBuckets(CarbonInterface $from, CarbonInterface $to): array
    {
        $buckets = [];
        $cursor = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $buckets[$key] = [
                'impressions' => 0,
                'likes' => 0,
                'comments' => 0,
                'reposts' => 0,
                'saves' => 0,
                'posts' => 0,
                'count' => 0,
                'rate_sum' => 0.0,
                'rate_n' => 0,
                'norm_sum' => 0.0,
                'norm_n' => 0,
                'hook_sum' => 0.0,
                'hook_n' => 0,
            ];
            $cursor->addDay();
        }

        return $buckets;
    }

    private function rangeLabel(?string $preset, CarbonInterface $from, CarbonInterface $to): string
    {
        if ($preset === '7d') {
            return 'Last 7 days';
        }
        if ($preset === '30d') {
            return 'Last 30 days';
        }
        if ($preset === '90d') {
            return 'Last 90 days';
        }

        return $from->format('M j').' – '.$to->format('M j, Y');
    }

    private function deltaPct(int|float|null $current, int|float|null $previous): ?float
    {
        if ($current === null || $previous === null) {
            return null;
        }
        $prev = (float) $previous;
        $cur = (float) $current;
        if ($prev == 0.0) {
            return $cur > 0 ? 100.0 : 0.0;
        }

        return round((($cur - $prev) / $prev) * 100, 1);
    }
}

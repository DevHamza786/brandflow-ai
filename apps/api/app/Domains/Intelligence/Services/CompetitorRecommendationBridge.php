<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\CompetitorSnapshotRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorDto;
use App\Domains\Intelligence\Data\CompetitorSnapshotDto;
use App\Domains\Recommendations\Contracts\RecommendationRepositoryContract;
use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationEvidenceDto;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationType;
use App\Domains\Recommendations\Services\RecommendationScoringService;

/**
 * Pushes competitor-derived insights into the recommendations store.
 */
final class CompetitorRecommendationBridge
{
    public function __construct(
        private readonly CompetitorSnapshotRepositoryContract $snapshots,
        private readonly RecommendationRepositoryContract $recommendations,
        private readonly RecommendationScoringService $scoring,
    ) {
    }

    public function syncForCompetitor(string $workspaceId, string $competitorId, CompetitorDto $competitor): int
    {
        $latest = $this->snapshots->findLatestByCompetitor($workspaceId, $competitorId);
        if ($latest === null) {
            return 0;
        }

        return $this->syncFromSnapshot($competitor, $latest)
            + $this->syncBenchmarkGap($competitor, $latest);
    }

    public function syncFromSnapshot(CompetitorDto $competitor, CompetitorSnapshotDto $snapshot): int
    {
        $created = 0;
        $insights = $snapshot->hookPatterns['insights'] ?? [];
        if (! is_array($insights)) {
            return 0;
        }

        foreach ($insights as $insight) {
            if (! is_array($insight) || ($insight['kind'] ?? '') !== 'style_gap') {
                continue;
            }
            $gap = (float) ($insight['gap_pct'] ?? 0);
            if ($gap < (float) config('intelligence.min_style_uplift_pct', 15.0)) {
                continue;
            }

            $key = 'competitor:'.$competitor->id.':style_gap:'.($insight['best_style'] ?? 'x');
            $draft = new CreateRecommendationDto(
                workspaceId: $competitor->workspaceId,
                type: RecommendationType::HookStyle,
                source: RecommendationSource::CompetitorIntelligence,
                correlationKey: $key,
                title: 'Competitor hook pattern advantage',
                summary: (string) ($insight['summary'] ?? ''),
                rationale: 'Competitor snapshot hook-style correlation for strategic positioning vs '.($competitor->name ?? 'tracked profile'),
                score: 0,
                confidence: null,
                evidence: [],
                personalizationContext: [
                    'competitor_id' => $competitor->id,
                    'competitor_name' => $competitor->name,
                    'niche_labels' => $competitor->labels,
                ],
                actionPayload: [
                    'action' => 'adopt_competitor_hook_style',
                    'style' => $insight['best_style'] ?? null,
                    'avoid_style' => $insight['worst_style'] ?? null,
                ],
            );

            $scored = $this->scoring->apply(
                $draft,
                new RecommendationEvidenceDto(
                    insightKind: 'competitor_style_gap',
                    sampleSize: $snapshot->postsCount,
                    baselineValue: null,
                    observedValue: $gap,
                    upliftPct: $gap,
                    metrics: $insight,
                ),
            );

            if ($scored->score < (int) config('recommendations.min_score_to_persist', 35)) {
                continue;
            }

            $this->recommendations->supersedeActiveByCorrelationKey($competitor->workspaceId, $key);
            $this->recommendations->create($scored);
            $created++;
        }

        return $created;
    }

    private function syncBenchmarkGap(CompetitorDto $competitor, CompetitorSnapshotDto $snapshot): int
    {
        $benchmark = is_array($snapshot->trendSummary['benchmark'] ?? null)
            ? $snapshot->trendSummary['benchmark']
            : null;
        if ($benchmark === null || empty($benchmark['competitor_ahead'])) {
            return 0;
        }

        $delta = $benchmark['delta_pct'] ?? null;
        if ($delta === null || (float) $delta < 10.0) {
            return 0;
        }

        $key = 'competitor:'.$competitor->id.':benchmark_ahead';
        $draft = new CreateRecommendationDto(
            workspaceId: $competitor->workspaceId,
            type: RecommendationType::EngagementImprovement,
            source: RecommendationSource::CompetitorIntelligence,
            correlationKey: $key,
            title: 'Competitor engagement benchmark gap',
            summary: sprintf(
                'Tracked competitor averages %.1f%% higher engagement than your workspace posts in the same period.',
                (float) $delta,
            ),
            rationale: 'Engagement benchmarking engine vs post_performance_snapshots.',
            score: 0,
            confidence: null,
            evidence: [],
            personalizationContext: [
                'competitor_id' => $competitor->id,
            ],
            actionPayload: ['action' => 'close_engagement_gap', 'target_delta_pct' => $delta],
        );

        $scored = $this->scoring->apply(
            $draft,
            new RecommendationEvidenceDto(
                insightKind: 'competitor_benchmark',
                sampleSize: (int) ($benchmark['competitor_posts_observed'] ?? 0),
                baselineValue: isset($benchmark['workspace_avg_engagement_rate']) ? (float) $benchmark['workspace_avg_engagement_rate'] : null,
                observedValue: isset($benchmark['competitor_avg_engagement_rate']) ? (float) $benchmark['competitor_avg_engagement_rate'] : null,
                upliftPct: (float) $delta,
                metrics: $benchmark,
            ),
        );

        if ($scored->score < (int) config('recommendations.min_score_to_persist', 35)) {
            return 0;
        }

        $this->recommendations->supersedeActiveByCorrelationKey($competitor->workspaceId, $key);
        $this->recommendations->create($scored);

        return 1;
    }
}

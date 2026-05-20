<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\CompetitorMlCompatibilityLayerContract;
use App\Domains\Intelligence\Contracts\CompetitorRepositoryContract;
use App\Domains\Intelligence\Contracts\CompetitorSnapshotRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorSnapshotDto;
use App\Domains\Intelligence\Support\CompetitorPayloadNormalizer;

/**
 * Runs intelligence pipeline on a snapshot and persists derived columns.
 */
final class CompetitorAnalyticsService
{
    public function __construct(
        private readonly CompetitorSnapshotRepositoryContract $snapshots,
        private readonly CompetitorRepositoryContract $competitors,
        private readonly CompetitorPayloadNormalizer $normalizer,
        private readonly HookPatternExtractionEngine $hookPatterns,
        private readonly PostingFrequencyAnalyzer $postingFrequency,
        private readonly EngagementBenchmarkingEngine $benchmarking,
        private readonly CompetitorTrendAnalysisService $trends,
        private readonly CompetitorScoringEngine $scoring,
        private readonly CompetitorMlCompatibilityLayerContract $mlLayer,
        private readonly CompetitorExecutionLogger $logger,
    ) {
    }

    public function analyzeSnapshot(string $workspaceId, string $snapshotId): CompetitorSnapshotDto
    {
        $snapshot = $this->snapshots->findById($workspaceId, $snapshotId);
        if ($snapshot === null) {
            throw new \InvalidArgumentException('Snapshot not found.');
        }

        $posts = $this->normalizer->posts($snapshot->payload);
        $rates = [];
        $ctaCounts = [];
        $lengths = [];

        foreach ($posts as $post) {
            $rates[] = $this->normalizer->engagementRate(
                (int) $post['impressions'],
                (int) $post['likes'],
                (int) $post['comments'],
                (int) $post['reposts'],
                (int) $post['saves'],
            );
            if (! empty($post['cta_text'])) {
                $cta = (string) $post['cta_text'];
                $ctaCounts[$cta] = ($ctaCounts[$cta] ?? 0) + 1;
            }
            $len = mb_strlen((string) ($post['hook_text'] ?? ''));
            if ($len > 0) {
                $lengths[] = $len;
            }
        }

        $avgRate = $rates !== [] ? array_sum($rates) / count($rates) : 0.0;
        $hookPatternData = $this->hookPatterns->extract($posts);
        $cadence = $this->postingFrequency->analyze($posts, $snapshot->capturedAt);
        $benchmark = $this->benchmarking->benchmark($workspaceId, $avgRate, count($posts));

        $analytics = [
            'posts_count' => count($posts),
            'avg_engagement_rate' => round($avgRate, 8),
            'posts_per_week' => $cadence['posts_per_week'] ?? 0,
            'engagement_metrics' => [
                'total_impressions' => array_sum(array_map(static fn ($p) => (int) $p['impressions'], $posts)),
                'total_likes' => array_sum(array_map(static fn ($p) => (int) $p['likes'], $posts)),
                'total_comments' => array_sum(array_map(static fn ($p) => (int) $p['comments'], $posts)),
                'avg_engagement_rate' => round($avgRate, 8),
            ],
            'hook_patterns' => $hookPatternData,
            'posting_cadence' => $cadence,
            'content_structure' => [
                'avg_hook_length' => $lengths !== [] ? (int) round(array_sum($lengths) / count($lengths)) : 0,
                'posts_with_body_preview' => count(array_filter($posts, static fn ($p) => ($p['body_preview'] ?? '') !== '')),
            ],
            'cta_patterns' => [
                'top_ctas' => $this->topCounts($ctaCounts, 5),
            ],
        ];

        $score = $this->scoring->score(
            $avgRate,
            count($posts),
            (float) ($cadence['posts_per_week'] ?? 0),
            $hookPatternData,
            $benchmark,
        );

        $analytics['intelligence_score'] = $score;
        $analytics['ml_features'] = $this->mlLayer->buildFeatures($analytics, $snapshot->payload);

        $updated = $this->snapshots->updateAnalytics($workspaceId, $snapshotId, $analytics);
        $trendSummary = array_merge(
            $this->trends->analyze($workspaceId, $snapshot->competitorId, $updated),
            ['benchmark' => $benchmark],
        );
        $updated = $this->snapshots->updateAnalytics($workspaceId, $snapshotId, [
            'trend_summary' => $trendSummary,
        ]);

        $this->competitors->updateIntelligenceScore($workspaceId, $snapshot->competitorId, $score);

        $this->logger->info('snapshot_analyzed', [
            'workspace_id' => $workspaceId,
            'snapshot_id' => $snapshotId,
            'posts_count' => count($posts),
            'intelligence_score' => $score,
        ]);

        return $updated;
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array{cta:string,count:int}>
     */
    private function topCounts(array $counts, int $limit): array
    {
        arsort($counts);
        $out = [];
        foreach (array_slice($counts, 0, $limit, true) as $cta => $count) {
            $out[] = ['cta' => $cta, 'count' => $count];
        }

        return $out;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;

/**
 * Benchmark competitor snapshot vs workspace owned performance.
 */
final class EngagementBenchmarkingEngine
{
    public function __construct(
        private readonly PostPerformanceSnapshotRepositoryContract $workspaceSnapshots,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function benchmark(string $workspaceId, float $competitorAvgRate, int $competitorPosts): array
    {
        $days = (int) config('intelligence.benchmark_lookback_days', 90);
        $rows = $this->workspaceSnapshots->listRecentForWorkspace($workspaceId, $days, 500);

        $rates = [];
        foreach ($rows as $row) {
            if ($row->engagementRate !== null) {
                $rates[] = (float) $row->engagementRate;
            }
        }

        $workspaceAvg = $rates !== [] ? array_sum($rates) / count($rates) : 0.0;
        $deltaPct = $workspaceAvg > 0
            ? (($competitorAvgRate - $workspaceAvg) / $workspaceAvg) * 100
            : null;

        return [
            'workspace_posts_observed' => count($rows),
            'workspace_avg_engagement_rate' => round($workspaceAvg, 6),
            'competitor_avg_engagement_rate' => round($competitorAvgRate, 6),
            'competitor_posts_observed' => $competitorPosts,
            'delta_pct' => $deltaPct !== null ? round($deltaPct, 1) : null,
            'competitor_ahead' => $deltaPct !== null && $deltaPct > 0,
        ];
    }
}

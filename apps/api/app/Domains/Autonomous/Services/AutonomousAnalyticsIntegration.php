<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Analytics\Services\AnalyticsDashboardService;
use Carbon\Carbon;

/**
 * Analytics-aware context for autonomous decisions (no raw event scans).
 */
final class AutonomousAnalyticsIntegration
{
    public function __construct(
        private readonly AnalyticsDashboardService $dashboard,
        private readonly PostPerformanceSnapshotRepositoryContract $snapshots,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSummary(string $workspaceId): array
    {
        $to = Carbon::now()->endOfDay();
        $from = $to->copy()->subDays(30)->startOfDay();
        $dash = $this->dashboard->build($workspaceId, $from, $to, '30d');
        $kpis = $dash->kpis;
        $histogram = $this->snapshots->postingHourHistogram($workspaceId, 30);
        $top = $this->snapshots->topByNormalizedEngagement($workspaceId, 5);

        $bestHour = null;
        if ($histogram !== []) {
            usort($histogram, static fn ($a, $b) => $b['avg_normalized'] <=> $a['avg_normalized']);
            $bestHour = $histogram[0];
        }

        $normAvg = $kpis['normalized_engagement_avg'] ?? null;

        return [
            'engagement_rate_avg' => $kpis['engagement_rate_avg'] ?? null,
            'normalized_engagement_avg' => $normAvg,
            'posts_observed' => $kpis['posts_observed'] ?? 0,
            'engagement_rate_delta_pct' => $dash->comparison['engagement_rate_delta'] ?? null,
            'best_posting_hour' => $bestHour,
            'posting_hour_histogram' => array_slice($histogram, 0, 12),
            'top_performer_count' => count($top),
            'low_engagement_period' => is_numeric($normAvg) && (float) $normAvg < 0.02,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\CompetitorSnapshotRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorSnapshotDto;

/**
 * Trend deltas across recent competitor snapshots.
 */
final class CompetitorTrendAnalysisService
{
    public function __construct(
        private readonly CompetitorSnapshotRepositoryContract $snapshots,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function analyze(string $workspaceId, string $competitorId, CompetitorSnapshotDto $current): array
    {
        $history = $this->snapshots->listRecentByCompetitor($workspaceId, $competitorId, 5);
        if (count($history) < (int) config('intelligence.trend_snapshots_min', 2)) {
            return ['status' => 'insufficient_history', 'snapshot_count' => count($history)];
        }

        $previous = $history[1] ?? null;
        if ($previous === null) {
            return ['status' => 'insufficient_history'];
        }

        $rateDelta = null;
        if ($current->avgEngagementRate !== null && $previous->avgEngagementRate !== null && $previous->avgEngagementRate > 0) {
            $rateDelta = (($current->avgEngagementRate - $previous->avgEngagementRate) / $previous->avgEngagementRate) * 100;
        }

        $cadenceDelta = null;
        if ($current->postsPerWeek !== null && $previous->postsPerWeek !== null) {
            $cadenceDelta = $current->postsPerWeek - $previous->postsPerWeek;
        }

        return [
            'status' => 'ok',
            'engagement_rate_delta_pct' => $rateDelta !== null ? round($rateDelta, 1) : null,
            'posts_per_week_delta' => $cadenceDelta !== null ? round($cadenceDelta, 2) : null,
            'intelligence_score_delta' => ($current->intelligenceScore ?? 0) - ($previous->intelligenceScore ?? 0),
            'previous_captured_at' => $previous->capturedAt->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

/**
 * Composite intelligence score (0–100) for ranking competitors / snapshots.
 */
final class CompetitorScoringEngine
{
    /**
     * @param  array<string, mixed>  $hookPatterns
     * @param  array<string, mixed>  $benchmark
     */
    public function score(
        float $avgEngagementRate,
        int $postsCount,
        float $postsPerWeek,
        array $hookPatterns,
        array $benchmark,
    ): float {
        $engagementComponent = min(40.0, $avgEngagementRate * 400);
        $volumeComponent = min(25.0, $postsCount * 2.5);
        $cadenceComponent = min(15.0, $postsPerWeek * 3);
        $patternComponent = 0.0;
        $insights = $hookPatterns['insights'] ?? [];
        if (is_array($insights) && $insights !== []) {
            $patternComponent = 10.0;
        }
        $aheadBonus = ! empty($benchmark['competitor_ahead']) ? 10.0 : 0.0;

        return round(min(100.0, $engagementComponent + $volumeComponent + $cadenceComponent + $patternComponent + $aheadBonus), 4);
    }
}

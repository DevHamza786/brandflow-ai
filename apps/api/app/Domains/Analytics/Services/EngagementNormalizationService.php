<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

/**
 * Engagement rate helpers — deterministic, explainable (not ML).
 */
final class EngagementNormalizationService
{
    /**
     * Weighted interactions / impressions (0+ unbounded).
     */
    public function rawEngagementScore(
        int $impressions,
        int $likes,
        int $comments,
        int $reposts,
        int $saves,
    ): float {
        $base = max(1, $impressions);

        return (
            $likes
            + ($comments * 2.0)
            + ($reposts * 3.0)
            + ($saves * 2.0)
        ) / $base;
    }

    public function engagementRate(int $impressions, int $likes, int $comments, int $reposts, int $saves): float
    {
        return $this->rawEngagementScore($impressions, $likes, $comments, $reposts, $saves);
    }

    /** Squash to [0,1] for cross-post comparison (foundation for bandits / recommendations). */
    public function normalize(float $raw): float
    {
        return 1.0 - exp(-$raw);
    }
}

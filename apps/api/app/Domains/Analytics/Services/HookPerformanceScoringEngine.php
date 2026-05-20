<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

/**
 * Combines Hook Lab scores with observed engagement for longitudinal learning.
 */
final class HookPerformanceScoringEngine
{
    public function score(?array $hookPerformance, float $normalizedEngagement): ?float
    {
        if ($hookPerformance === null) {
            return null;
        }

        $overall = null;
        if (isset($hookPerformance['overall']) && is_numeric($hookPerformance['overall'])) {
            $overall = (float) $hookPerformance['overall'];
        }

        if ($overall === null) {
            return round(min(100.0, $normalizedEngagement * 100.0), 4);
        }

        $blend = (0.45 * $overall) + (0.55 * ($normalizedEngagement * 100.0));

        return round(min(100.0, max(0.0, $blend)), 4);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use App\Domains\Analytics\Services\AnalyticsQueryService;

/**
 * Read-only analytics linkage — never mutates analytics_events.
 */
final class ExperimentAnalyticsIntegration
{
    public function __construct(
        private readonly AnalyticsQueryService $analytics,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function contextRefs(string $workspaceId): array
    {
        return [
            'recent_event_count' => count($this->analytics->recentEvents($workspaceId, 5)),
            'ref_type' => 'analytics_read_only',
        ];
    }

    /**
     * Simulated observation metrics from analytics profile (safe read).
     *
     * @return list<array{impressions: int, engagements: int, normalized_score: float}>
     */
    public function syntheticObservationsForVariant(string $workspaceId, string $variantKey, int $count = 5): array
    {
        $profile = $this->analytics->postingTimeProfile($workspaceId, 14);
        $base = 0.04;
        foreach ($profile as $row) {
            if (($row['sample_count'] ?? 0) > 0) {
                $base = max($base, (float) ($row['avg_normalized'] ?? 0) / 100);
                break;
            }
        }

        $modifier = $variantKey === 'variant_a' ? 1.172 : 1.0;
        $observations = [];
        for ($i = 0; $i < $count; $i++) {
            $impressions = 1000 + ($i * 50);
            $rate = $base * $modifier * (0.95 + ($i * 0.01));
            $engagements = (int) round($impressions * $rate);
            $observations[] = [
                'impressions' => $impressions,
                'engagements' => $engagements,
                'normalized_score' => round($rate * 100, 2),
            ];
        }

        return $observations;
    }
}

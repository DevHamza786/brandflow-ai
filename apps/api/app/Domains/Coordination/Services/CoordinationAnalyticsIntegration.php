<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Analytics\Services\AnalyticsQueryService;

/**
 * Analytics-aware coordination — rollup refs only.
 */
final class CoordinationAnalyticsIntegration
{
    public function __construct(
        private readonly AnalyticsQueryService $analytics,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildContextRefs(string $workspaceId): array
    {
        $events = $this->analytics->recentEvents($workspaceId, 5);

        return [
            'has_data' => $events !== [],
            'event_count' => count($events),
            'ref_type' => 'analytics_events',
        ];
    }
}

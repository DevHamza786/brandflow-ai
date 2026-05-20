<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Contracts\AnalyticsEventRepositoryContract;
use App\Domains\Analytics\Data\AnalyticsEventDto;

/**
 * Read-side analytics API for agents, workflows, and future dashboards.
 */
final class AnalyticsQueryService
{
    public function __construct(
        private readonly AnalyticsEventRepositoryContract $events,
        private readonly BestPerformingVariantAnalyzer $variants,
        private readonly PostingTimeAnalyzer $postingTime,
    ) {
    }

    /**
     * @return list<AnalyticsEventDto>
     */
    public function recentEvents(string $workspaceId, int $limit = 50): array
    {
        return $this->events->listRecent($workspaceId, $limit);
    }

    /**
     * @return list<array{id:string,entity_id:string,normalized:?float,hook_score:?float,hook_text:?string}>
     */
    public function topPerformingHooks(string $workspaceId, int $limit = 10): array
    {
        return $this->variants->topHooks($workspaceId, $limit);
    }

    /**
     * @return list<array{hour:int,sample_count:int,avg_normalized:float}>
     */
    public function postingTimeProfile(string $workspaceId, int $daysBack = 30): array
    {
        return $this->postingTime->hourlyEngagementProfile($workspaceId, $daysBack);
    }
}

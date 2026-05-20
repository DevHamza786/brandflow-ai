<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Services;

use App\Domains\Analytics\Services\AnalyticsQueryService;

final class WorkflowBuilderAnalyticsIntegration
{
    public function __construct(
        private readonly AnalyticsQueryService $analytics,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function executionContextRefs(string $workspaceId): array
    {
        return [
            'event_count' => count($this->analytics->recentEvents($workspaceId, 3)),
            'ref_type' => 'analytics',
        ];
    }
}

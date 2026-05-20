<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Recommendations\Data\GenerateRecommendationsResultDto;
use App\Domains\Recommendations\Events\RecommendationsGenerated;
use Illuminate\Support\Facades\Event;

/**
 * Workflow / API entry — generate → persist → emit for downstream agents.
 */
final class RecommendationOrchestrationService
{
    public function __construct(
        private readonly RecommendationEngine $engine,
        private readonly RecommendationExecutionLogger $logger,
    ) {
    }

    public function generateAndPublish(string $workspaceId, ?int $lookbackDays = null): GenerateRecommendationsResultDto
    {
        $this->logger->info('orchestration_start', ['workspace_id' => $workspaceId]);

        $result = $this->engine->generate($workspaceId, $lookbackDays);

        Event::dispatch(new RecommendationsGenerated(
            workspaceId: $workspaceId,
            generatedCount: $result->generatedCount,
            countsByType: $result->countsByType,
        ));

        $this->logger->info('orchestration_complete', [
            'workspace_id' => $workspaceId,
            'generated_count' => $result->generatedCount,
        ]);

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Actions;

use App\Domains\Recommendations\Data\GenerateRecommendationsResultDto;
use App\Domains\Recommendations\Services\RecommendationOrchestrationService;

final class GenerateRecommendationsAction
{
    public function __construct(
        private readonly RecommendationOrchestrationService $orchestration,
    ) {
    }

    public function execute(string $workspaceId, ?int $lookbackDays = null): GenerateRecommendationsResultDto
    {
        return $this->orchestration->generateAndPublish($workspaceId, $lookbackDays);
    }
}

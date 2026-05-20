<?php

declare(strict_types=1);

namespace App\Domains\Agents\Actions;

use App\Domains\Agents\Data\AgentRunResultsDto;
use App\Domains\Agents\Services\ResultsQueryService;

final class GetWorkflowResultsAction
{
    public function __construct(
        private readonly ResultsQueryService $results,
    ) {
    }

    public function execute(string $workspaceId, string $agentRunId): AgentRunResultsDto
    {
        return $this->results->getResultsForAgentRun($workspaceId, $agentRunId);
    }
}

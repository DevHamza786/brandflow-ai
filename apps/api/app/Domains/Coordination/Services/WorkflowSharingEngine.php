<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Coordination\Data\AgentCoordinationDto;

/**
 * Shares workflow run / blueprint refs across coordinated agents.
 */
final class WorkflowSharingEngine
{
    /**
     * @return array<string, mixed>
     */
    public function buildContextRefs(AgentCoordinationDto $coordination): array
    {
        return [
            'workflow_run_id' => $coordination->workflowRunId,
            'workflow_blueprint_id' => $coordination->workflowBlueprintId,
            'ref_type' => 'workflow',
        ];
    }
}

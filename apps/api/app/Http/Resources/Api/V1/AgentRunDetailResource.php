<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Agents\Data\AgentRunResultsDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Agent run detail with links to normalized results (polling entry).
 *
 * @property AgentRunResultsDto $resource
 */
final class AgentRunDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AgentRunResultsDto $results */
        $results = $this->resource;
        $agentRun = $results->agentRun;
        $workflowRun = $results->workflowRun;

        return [
            'status' => $results->status,
            'agent_run' => $agentRun !== null ? AgentRunResource::make($agentRun) : null,
            'workflow_run' => $workflowRun !== null ? WorkflowRunResource::make($workflowRun) : null,
            'results_url' => $agentRun !== null
                ? url('/api/v1/agents/runs/'.$agentRun->id.'/results')
                : null,
            'timestamps' => $results->timestamps,
        ];
    }
}

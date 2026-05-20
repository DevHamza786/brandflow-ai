<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Agents\Actions\GetWorkflowResultsAction;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveWorkspace;
use App\Http\Resources\Api\V1\AgentRunDetailResource;
use App\Http\Resources\Api\V1\AgentRunResultsResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Polling-friendly retrieval for agent / workflow execution results.
 */
final class ResultsController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetWorkflowResultsAction $getResults,
    ) {
    }

    /**
     * GET /api/v1/agents/runs/{id}
     */
    public function show(Request $request, string $agentRunId): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);

        try {
            $results = $this->getResults->execute($workspaceId, $agentRunId);
        } catch (ModelNotFoundException) {
            return $this->problem(
                status: 404,
                type: 'https://pbos.dev/problems/agent-run-not-found',
                title: 'Agent Run Not Found',
                detail: 'The requested agent run does not exist in this workspace.',
            );
        } catch (Throwable $e) {
            Log::error('results.api.show_failed', [
                'workspace_id' => $workspaceId,
                'agent_run_id' => $agentRunId,
                'message' => $e->getMessage(),
            ]);

            return $this->problem(
                status: 500,
                type: 'https://pbos.dev/problems/results-unavailable',
                title: 'Results Unavailable',
                detail: 'Unable to load agent run details.',
            );
        }

        Log::info('results.api.show', [
            'workspace_id' => $workspaceId,
            'agent_run_id' => $agentRunId,
            'status' => $results->status,
        ]);

        return $this->success(AgentRunDetailResource::make($results));
    }

    /**
     * GET /api/v1/agents/runs/{id}/results
     */
    public function results(Request $request, string $agentRunId): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);

        try {
            $results = $this->getResults->execute($workspaceId, $agentRunId);
        } catch (ModelNotFoundException) {
            return $this->problem(
                status: 404,
                type: 'https://pbos.dev/problems/agent-run-not-found',
                title: 'Agent Run Not Found',
                detail: 'The requested agent run does not exist in this workspace.',
            );
        } catch (Throwable $e) {
            Log::error('results.api.results_failed', [
                'workspace_id' => $workspaceId,
                'agent_run_id' => $agentRunId,
                'message' => $e->getMessage(),
            ]);

            return $this->problem(
                status: 500,
                type: 'https://pbos.dev/problems/results-unavailable',
                title: 'Results Unavailable',
                detail: 'Unable to load workflow results.',
            );
        }

        Log::debug('results.api.poll', [
            'workspace_id' => $workspaceId,
            'agent_run_id' => $agentRunId,
            'status' => $results->status,
            'output_count' => count($results->outputs),
        ]);

        return $this->success(AgentRunResultsResource::make($results));
    }
}

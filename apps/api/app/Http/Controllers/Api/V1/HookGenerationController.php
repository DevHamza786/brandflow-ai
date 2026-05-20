<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Agents\Agents\HookAgent\Exceptions\HookAgentException;
use App\Domains\Agents\Agents\HookAgent\Exceptions\HookContentNotFoundException;
use App\Domains\Content\Services\HookWorkflowService;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveWorkspace;
use App\Http\Requests\Api\V1\GenerateHooksRequest;
use App\Http\Resources\Api\V1\HookGenerationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Hook Lab — async hook scoring and variant generation.
 */
final class HookGenerationController extends Controller
{
    public function __construct(
        private readonly HookWorkflowService $workflow,
    ) {
    }

    public function store(GenerateHooksRequest $request): JsonResponse
    {
        try {
            $result = $this->workflow->start(
                workspaceId: $request->workspaceId(),
                config: $request->toHookAgentConfig(),
                idempotencyKey: $request->idempotencyKey(),
            );
        } catch (HookContentNotFoundException $e) {
            return $this->problem(
                status: 404,
                type: 'https://pbos.dev/problems/content-not-found',
                title: 'Content Not Found',
                detail: $e->getMessage(),
            );
        } catch (HookAgentException $e) {
            return $this->problem(
                status: 422,
                type: 'https://pbos.dev/problems/hook-validation',
                title: 'Hook Validation Failed',
                detail: $e->getMessage(),
                context: $e->context,
            );
        }

        $status = $result->wasReplayed ? 200 : 202;

        Log::info('hook.api.accepted', [
            'workspace_id' => $request->workspaceId(),
            'agent_run_id' => $result->agentRun->id,
            'workflow_run_id' => $result->workflowRun->id,
            'was_replayed' => $result->wasReplayed,
        ]);

        return HookGenerationResource::make($result)
            ->response()
            ->setStatusCode($status);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function problem(
        int $status,
        string $type,
        string $title,
        string $detail,
        array $context = [],
    ): JsonResponse {
        $body = [
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
        ];

        if ($context !== []) {
            $body['context'] = $context;
        }

        return response()->json($body, $status, [
            'Content-Type' => 'application/problem+json',
        ]);
    }
}

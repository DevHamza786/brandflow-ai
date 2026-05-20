<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Content\Data\HookGenerationResultDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HookGenerationResultDto
 */
final class HookGenerationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var HookGenerationResultDto $result */
        $result = $this->resource;

        return [
            'agent_run' => AgentRunResource::make($result->agentRun),
            'workflow_run' => [
                'id' => $result->workflowRun->id,
                'status' => $result->workflowRun->status,
                'workflow_slug' => $result->workflowRun->context['workflow_slug'] ?? 'hook_generation',
                'context' => $result->workflowRun->context ?? [],
                'started_at' => $result->workflowRun->started_at?->toIso8601String(),
                'completed_at' => $result->workflowRun->completed_at?->toIso8601String(),
            ],
            'hook_score' => $result->hookScore !== null
                ? HookScoreResource::make($result->hookScore)
                : null,
            'was_replayed' => $result->wasReplayed,
            'poll_url' => url('/api/v1/agents/runs/'.$result->agentRun->id.'/results'),
            'detail_url' => url('/api/v1/agents/runs/'.$result->agentRun->id),
        ];
    }
}

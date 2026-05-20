<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Workflows\Models\WorkflowRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WorkflowRun
 */
final class WorkflowRunResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $context = $this->context ?? [];

        return [
            'id' => $this->id,
            'status' => $this->status,
            'workflow_slug' => (string) ($context['workflow_slug'] ?? ''),
            'current_step_id' => $this->current_step_id,
            'context' => [
                'content_version_id' => $context['content_version_id'] ?? null,
                'agent_run_id' => $context['agent_run_id'] ?? null,
                'generated_output_id' => $context['generated_output_id'] ?? null,
                'hook_score_id' => $context['hook_score_id'] ?? null,
            ],
            'error' => $this->when($this->status === 'failed', $this->error),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

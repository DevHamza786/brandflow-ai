<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Agents\Models\AgentRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AgentRun
 */
final class AgentRunResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'status' => $this->status,
            'input' => $this->input ?? [],
            'options' => $this->options ?? [],
            'output' => $this->when(
                in_array($this->status, ['completed', 'failed'], true),
                $this->output,
            ),
            'error' => $this->when($this->status === 'failed', $this->error),
            'trace_id' => $this->trace_id,
            'workflow_run_id' => $this->options['workflow_run_id'] ?? null,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

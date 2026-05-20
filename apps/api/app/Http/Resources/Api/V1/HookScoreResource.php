<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Content\Models\HookScore;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HookScore
 */
final class HookScoreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content_version_id' => $this->content_version_id,
            'agent_run_id' => $this->agent_run_id,
            'score' => (float) $this->score,
            'dimensions' => $this->dimensions ?? [],
            'variants' => $this->variants ?? [],
            'suggestions' => $this->suggestions ?? [],
            'model' => $this->model,
            'prompt_version' => $this->prompt_version,
            'trace_id' => $this->trace_id,
            'metadata' => $this->metadata ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

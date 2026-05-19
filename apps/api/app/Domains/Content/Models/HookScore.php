<?php

declare(strict_types=1);

namespace App\Domains\Content\Models;

use App\Domains\Agents\Models\AgentRun;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HookScore extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'content_version_id',
        'agent_run_id',
        'score',
        'dimensions',
        'variants',
        'suggestions',
        'model',
        'prompt_version',
        'trace_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'dimensions' => 'array',
            'variants' => 'array',
            'suggestions' => 'array',
            'metadata' => 'array',
        ];
    }

    public function agentRun(): BelongsTo
    {
        return $this->belongsTo(AgentRun::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\AI\Models;

use App\Domains\Agents\Models\AgentRun;
use App\Domains\Content\Models\ContentVersion;
use App\Domains\Workflows\Models\WorkflowRun;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Thin Eloquent model — business rules live in GeneratedOutputService.
 *
 * JSON columns use native array casts; structured access via DTOs at service boundary.
 */
class GeneratedOutput extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'workflow_run_id',
        'agent_run_id',
        'content_version_id',
        'type',
        'provider',
        'model',
        'prompt_version',
        'input',
        'output',
        'scores',
        'metadata',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'output' => 'array',
            'scores' => 'array',
            'metadata' => 'array',
        ];
    }

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class);
    }

    public function agentRun(): BelongsTo
    {
        return $this->belongsTo(AgentRun::class);
    }

    public function contentVersion(): BelongsTo
    {
        return $this->belongsTo(ContentVersion::class);
    }
}

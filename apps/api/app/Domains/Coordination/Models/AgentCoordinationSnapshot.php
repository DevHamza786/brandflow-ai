<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AgentCoordinationSnapshot extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'agent_coordination_id',
        'snapshot_type',
        'cycle_number',
        'role_slug',
        'task_type',
        'agent_slug',
        'routed_agent_slug',
        'handler_type',
        'status',
        'context_refs',
        'payload',
        'error',
        'idempotency_key',
        'trace_id',
        'agent_run_id',
        'priority',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'context_refs' => 'array',
            'payload' => 'array',
            'error' => 'array',
        ];
    }

    public function coordination(): BelongsTo
    {
        return $this->belongsTo(AgentCoordination::class, 'agent_coordination_id');
    }
}

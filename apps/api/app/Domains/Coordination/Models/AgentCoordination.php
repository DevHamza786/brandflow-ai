<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AgentCoordination extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'status',
        'coordination_mode',
        'correlation_key',
        'current_cycle',
        'workflow_run_id',
        'workflow_blueprint_id',
        'optimization_loop_id',
        'autonomous_workflow_id',
        'shared_context',
        'config',
        'ml_state',
        'metadata',
        'lock_token',
        'locked_at',
        'started_at',
        'last_run_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'shared_context' => 'array',
            'config' => 'array',
            'ml_state' => 'array',
            'metadata' => 'array',
            'locked_at' => 'datetime',
            'started_at' => 'datetime',
            'last_run_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(AgentCoordinationSnapshot::class);
    }
}

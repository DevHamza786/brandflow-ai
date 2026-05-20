<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AutonomousWorkflow extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'status',
        'mode',
        'correlation_key',
        'current_cycle',
        'optimization_loop_id',
        'workflow_run_id',
        'config',
        'ml_state',
        'metadata',
        'manual_override_enabled',
        'autonomous_execution_enabled',
        'locked_at',
        'lock_token',
        'started_at',
        'last_run_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'ml_state' => 'array',
            'metadata' => 'array',
            'manual_override_enabled' => 'boolean',
            'autonomous_execution_enabled' => 'boolean',
            'locked_at' => 'datetime',
            'started_at' => 'datetime',
            'last_run_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(AutonomousExecutionSnapshot::class);
    }
}

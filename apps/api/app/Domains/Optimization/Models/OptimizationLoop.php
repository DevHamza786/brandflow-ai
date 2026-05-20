<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OptimizationLoop extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'loop_type',
        'status',
        'correlation_key',
        'current_cycle',
        'config',
        'ml_state',
        'metadata',
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
            'started_at' => 'datetime',
            'last_run_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(OptimizationSnapshot::class);
    }
}

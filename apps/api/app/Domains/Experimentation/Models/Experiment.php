<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Experiment extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'slug',
        'name',
        'experiment_type',
        'status',
        'hypothesis',
        'config',
        'ml_state',
        'metadata',
        'optimization_loop_id',
        'workflow_blueprint_id',
        'agent_coordination_id',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'ml_state' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ExperimentVariant::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExperimentResult::class);
    }
}

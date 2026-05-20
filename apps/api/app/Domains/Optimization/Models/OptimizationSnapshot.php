<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OptimizationSnapshot extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'optimization_loop_id',
        'cycle_number',
        'status',
        'engine',
        'focus',
        'score',
        'confidence',
        'title',
        'summary',
        'rationale',
        'baseline_metrics',
        'observed_metrics',
        'delta_metrics',
        'evidence',
        'action_payload',
        'personalization_context',
        'ml_features',
        'captured_at',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'baseline_metrics' => 'array',
            'observed_metrics' => 'array',
            'delta_metrics' => 'array',
            'evidence' => 'array',
            'action_payload' => 'array',
            'personalization_context' => 'array',
            'ml_features' => 'array',
            'captured_at' => 'datetime',
            'confidence' => 'float',
        ];
    }

    public function loop(): BelongsTo
    {
        return $this->belongsTo(OptimizationLoop::class, 'optimization_loop_id');
    }
}

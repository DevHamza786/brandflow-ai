<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AutonomousExecutionSnapshot extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'autonomous_workflow_id',
        'cycle_number',
        'status',
        'decision_type',
        'engine',
        'focus',
        'score',
        'confidence',
        'title',
        'summary',
        'rationale',
        'blocked_reason',
        'decision_payload',
        'evidence',
        'action_payload',
        'personalization_context',
        'ml_features',
        'recommendation_id',
        'scheduled_post_id',
        'generated_output_id',
        'captured_at',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'decision_payload' => 'array',
            'evidence' => 'array',
            'action_payload' => 'array',
            'personalization_context' => 'array',
            'ml_features' => 'array',
            'captured_at' => 'datetime',
            'confidence' => 'float',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(AutonomousWorkflow::class, 'autonomous_workflow_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Models;

use App\Domains\AI\Models\GeneratedOutput;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowRun extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'workflow_id',
        'status',
        'context',
        'error',
        'current_step_id',
        'idempotency_key',
        'started_at',
        'completed_at',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'error' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function generatedOutputs(): HasMany
    {
        return $this->hasMany(GeneratedOutput::class);
    }
}

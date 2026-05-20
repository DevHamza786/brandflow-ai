<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ExperimentResult extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'experiment_id',
        'experiment_variant_id',
        'result_type',
        'entity_type',
        'entity_id',
        'subject_key',
        'metrics',
        'statistical_summary',
        'idempotency_key',
        'trace_id',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'statistical_summary' => 'array',
        ];
    }

    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ExperimentVariant::class, 'experiment_variant_id');
    }
}

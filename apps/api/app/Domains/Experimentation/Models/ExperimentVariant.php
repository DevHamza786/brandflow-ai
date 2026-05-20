<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ExperimentVariant extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'experiment_id',
        'variant_key',
        'label',
        'is_control',
        'traffic_weight',
        'payload',
        'metadata',
        'assignment_count',
    ];

    protected function casts(): array
    {
        return [
            'is_control' => 'boolean',
            'traffic_weight' => 'float',
            'payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExperimentResult::class);
    }
}

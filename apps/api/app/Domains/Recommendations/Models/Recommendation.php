<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class Recommendation extends Model
{
    use HasUuids;

    protected $table = 'recommendations';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'workspace_id',
        'type',
        'status',
        'source',
        'correlation_key',
        'title',
        'summary',
        'rationale',
        'score',
        'confidence',
        'evidence',
        'personalization_context',
        'action_payload',
        'ml_state',
        'generated_at',
        'valid_from',
        'valid_until',
        'superseded_by_id',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'personalization_context' => 'array',
            'action_payload' => 'array',
            'ml_state' => 'array',
            'generated_at' => 'datetime',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'confidence' => 'float',
        ];
    }
}

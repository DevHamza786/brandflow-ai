<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    use HasUuids;

    protected $table = 'analytics_events';

    protected $fillable = [
        'workspace_id',
        'event_type',
        'entity_type',
        'entity_id',
        'properties',
        'occurred_at',
        'idempotency_key',
        'user_id',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EngagementMetric extends Model
{
    use HasUuids;

    protected $table = 'engagement_metrics';

    protected $fillable = [
        'workspace_id',
        'measurable_type',
        'measurable_id',
        'metric_date',
        'metric_type',
        'value',
        'dimensions',
        'source',
        'captured_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'dimensions' => 'array',
            'metadata' => 'array',
            'captured_at' => 'datetime',
        ];
    }
}

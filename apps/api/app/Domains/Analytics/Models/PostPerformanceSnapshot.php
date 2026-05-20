<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PostPerformanceSnapshot extends Model
{
    use HasUuids;

    protected $table = 'post_performance_snapshots';

    protected $fillable = [
        'workspace_id',
        'entity_type',
        'entity_id',
        'provider_post_id',
        'posted_at',
        'observed_at',
        'impressions',
        'likes',
        'comments',
        'reposts',
        'saves',
        'engagement_rate',
        'normalized_engagement',
        'hook_performance',
        'content_features',
        'ml_features',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'observed_at' => 'datetime',
            'hook_performance' => 'array',
            'content_features' => 'array',
            'ml_features' => 'array',
            'metadata' => 'array',
            'impressions' => 'integer',
            'likes' => 'integer',
            'comments' => 'integer',
            'reposts' => 'integer',
            'saves' => 'integer',
            'engagement_rate' => 'float',
            'normalized_engagement' => 'float',
        ];
    }
}

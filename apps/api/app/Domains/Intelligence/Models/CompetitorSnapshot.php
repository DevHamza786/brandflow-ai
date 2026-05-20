<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CompetitorSnapshot extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'competitor_id',
        'captured_at',
        'payload',
        'content_hash',
        'metadata',
        'posts_count',
        'avg_engagement_rate',
        'posts_per_week',
        'intelligence_score',
        'engagement_metrics',
        'hook_patterns',
        'posting_cadence',
        'content_structure',
        'cta_patterns',
        'trend_summary',
        'ml_features',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'payload' => 'array',
            'metadata' => 'array',
            'engagement_metrics' => 'array',
            'hook_patterns' => 'array',
            'posting_cadence' => 'array',
            'content_structure' => 'array',
            'cta_patterns' => 'array',
            'trend_summary' => 'array',
            'ml_features' => 'array',
            'avg_engagement_rate' => 'float',
            'posts_per_week' => 'float',
            'intelligence_score' => 'float',
        ];
    }

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }
}

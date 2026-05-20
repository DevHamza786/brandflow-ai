<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Competitor extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'linkedin_url',
        'name',
        'linkedin_urn',
        'labels',
        'metadata',
        'scrape_cadence_hours',
        'last_scraped_at',
        'last_analyzed_at',
        'intelligence_score',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'last_scraped_at' => 'datetime',
            'last_analyzed_at' => 'datetime',
            'intelligence_score' => 'float',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(CompetitorSnapshot::class);
    }
}

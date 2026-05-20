<?php

declare(strict_types=1);

namespace App\Domains\Brand\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Canonical workspace brand memory profile — rules live in BrandMemory* services.
 */
class BrandProfile extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'name',
        'voice',
        'pillars',
        'constraints',
        'brand_voice',
        'tone_profile',
        'target_audience',
        'banned_phrases',
        'preferred_ctas',
        'preferred_hook_patterns',
        'style_guidelines',
        'memory_version',
        'is_primary',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'voice' => 'array',
            'pillars' => 'array',
            'constraints' => 'array',
            'tone_profile' => 'array',
            'target_audience' => 'array',
            'banned_phrases' => 'array',
            'preferred_ctas' => 'array',
            'preferred_hook_patterns' => 'array',
            'style_guidelines' => 'array',
            'metadata' => 'array',
            'is_primary' => 'boolean',
            'memory_version' => 'integer',
        ];
    }

    public function writingSamples(): HasMany
    {
        return $this->hasMany(WritingSample::class);
    }
}

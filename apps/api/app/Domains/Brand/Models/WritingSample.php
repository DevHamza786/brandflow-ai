<?php

declare(strict_types=1);

namespace App\Domains\Brand\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representative writing used for style extraction and future RAG embeddings.
 */
class WritingSample extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'brand_profile_id',
        'content',
        'source_type',
        'metadata',
        'embedding_ready',
        'normalized_style_data',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'normalized_style_data' => 'array',
            'embedding_ready' => 'boolean',
        ];
    }

    public function brandProfile(): BelongsTo
    {
        return $this->belongsTo(BrandProfile::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'slug',
        'name',
        'definition',
        'version',
        'is_active',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'definition' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class);
    }
}

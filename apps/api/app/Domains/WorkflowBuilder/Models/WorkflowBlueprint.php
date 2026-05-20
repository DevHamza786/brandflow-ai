<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class WorkflowBlueprint extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'slug',
        'name',
        'status',
        'version',
        'is_active',
        'blueprint_type',
        'config',
        'ml_state',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'ml_state' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(WorkflowNode::class);
    }

    public function edges(): HasMany
    {
        return $this->hasMany(WorkflowEdge::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkflowNode extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'workflow_blueprint_id',
        'node_key',
        'node_type',
        'label',
        'config',
        'position',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'position' => 'array',
        ];
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(WorkflowBlueprint::class, 'workflow_blueprint_id');
    }
}

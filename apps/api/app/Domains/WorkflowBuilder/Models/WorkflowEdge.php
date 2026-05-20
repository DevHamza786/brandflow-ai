<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkflowEdge extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'workflow_blueprint_id',
        'from_node_key',
        'to_node_key',
        'edge_type',
        'condition',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'condition' => 'array',
            'metadata' => 'array',
        ];
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(WorkflowBlueprint::class, 'workflow_blueprint_id');
    }
}

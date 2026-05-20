<?php

declare(strict_types=1);

namespace App\Domains\Agents\Models;

use App\Domains\AI\Models\GeneratedOutput;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentRun extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'slug',
        'status',
        'input',
        'options',
        'output',
        'error',
        'trace_id',
        'idempotency_key',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'options' => 'array',
            'output' => 'array',
            'error' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function generatedOutputs(): HasMany
    {
        return $this->hasMany(GeneratedOutput::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Agents\Data;

use App\Domains\Agents\Models\AgentRun;
use App\Domains\Shared\Data\DataTransferObject;
use App\Domains\Workflows\Models\WorkflowRun;

/**
 * Stable read model for polling — shape is identical across queued/running/completed/failed.
 */
final class AgentRunResultsDto extends DataTransferObject
{
    /**
     * @param  list<array<string, mixed>>  $outputs
     * @param  array<string, mixed>  $scores
     * @param  array<string, mixed>  $metadata
     * @param  list<array<string, mixed>>  $variants
     * @param  array<string, mixed>  $dimensions
     * @param  list<string>  $suggestions
     * @param  array<string, mixed>|null  $error
     * @param  array<string, string|null>  $timestamps
     */
    public function __construct(
        public readonly string $status,
        public readonly array $outputs = [],
        public readonly array $scores = [],
        public readonly array $metadata = [],
        public readonly array $variants = [],
        public readonly array $dimensions = [],
        public readonly array $suggestions = [],
        public readonly ?array $error = null,
        public readonly array $timestamps = [],
        public readonly ?AgentRun $agentRun = null,
        public readonly ?WorkflowRun $workflowRun = null,
    ) {
    }
}

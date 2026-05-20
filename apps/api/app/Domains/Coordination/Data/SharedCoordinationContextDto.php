<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Reference-only shared context — avoids duplicating full prompts in each agent.
 *
 * @param  list<string>  $memoryChunkIds
 * @param  array<string, mixed>  $analyticsRefs
 * @param  array<string, mixed>  $optimizationRefs
 * @param  array<string, mixed>  $workflowRefs
 */
final class SharedCoordinationContextDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly ?int $memoryVersion,
        public readonly array $memoryChunkIds,
        public readonly array $analyticsRefs,
        public readonly array $optimizationRefs,
        public readonly array $workflowRefs,
        public readonly string $contextDigest,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toRefsArray(): array
    {
        return [
            'memory_version' => $this->memoryVersion,
            'memory_chunk_ids' => $this->memoryChunkIds,
            'analytics' => $this->analyticsRefs,
            'optimization' => $this->optimizationRefs,
            'workflow' => $this->workflowRefs,
            'context_digest' => $this->contextDigest,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Coordination\Data\SharedCoordinationContextDto;

/**
 * Propagates memory refs into agent run options (not full chunk content).
 */
final class AgentMemorySynchronization
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function mergeIntoAgentOptions(
        SharedCoordinationContextDto $context,
        array $options = [],
    ): array {
        return array_merge($options, [
            'coordination_context_digest' => $context->contextDigest,
            'memory_version' => $context->memoryVersion,
            'memory_chunk_ids' => $context->memoryChunkIds,
            'coordination_refs_only' => true,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\AI\Data\MemoryContext;
use App\Domains\Brand\Contracts\MemoryRetrievalServiceContract;

/**
 * Stub memory retrieval — wired for HookAgent; full RAG in a later iteration.
 */
final class MemoryRetrievalService implements MemoryRetrievalServiceContract
{
    /**
     * @param  list<string>  $types
     */
    public function retrieve(
        string $workspaceId,
        string $query,
        array $types = [],
        ?int $memoryVersion = null,
        int $limit = 5,
    ): MemoryContext {
        return new MemoryContext(
            workspaceId: $workspaceId,
            memoryVersion: $memoryVersion ?? 1,
            chunks: [],
        );
    }
}

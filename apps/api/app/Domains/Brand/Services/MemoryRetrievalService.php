<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\AI\Data\MemoryContext;
use App\Domains\Brand\Contracts\BrandMemoryQueryServiceContract;
use App\Domains\Brand\Contracts\MemoryRetrievalServiceContract;

/**
 * Workspace brand memory retrieval — delegates to BrandMemoryQueryService (vector search later).
 */
final class MemoryRetrievalService implements MemoryRetrievalServiceContract
{
    public function __construct(
        private readonly BrandMemoryQueryServiceContract $memoryQuery,
    ) {
    }

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
        $enrichment = $this->memoryQuery->enrichForWorkspace($workspaceId, $query, $limit);

        if ($memoryVersion !== null && $enrichment->profile !== null && $enrichment->memoryVersion !== $memoryVersion) {
            // Caller pinned an older version — still return current chunks but preserve requested version in context.
            return new MemoryContext(
                workspaceId: $workspaceId,
                memoryVersion: $memoryVersion,
                chunks: $this->filterChunksByTypes($enrichment->toMemoryContext()->chunks, $types),
            );
        }

        $context = $enrichment->toMemoryContext();

        return new MemoryContext(
            workspaceId: $context->workspaceId,
            memoryVersion: $context->memoryVersion,
            chunks: $this->filterChunksByTypes($context->chunks, $types),
        );
    }

    /**
     * @param  list<\App\Domains\AI\Data\MemoryChunkReference>  $chunks
     * @param  list<string>  $types
     * @return list<\App\Domains\AI\Data\MemoryChunkReference>
     */
    private function filterChunksByTypes(array $chunks, array $types): array
    {
        if ($types === []) {
            return $chunks;
        }

        $allowed = array_flip(array_map('strtolower', $types));

        return array_values(array_filter(
            $chunks,
            static fn ($chunk) => isset($allowed[strtolower($chunk->type)])
        ));
    }
}

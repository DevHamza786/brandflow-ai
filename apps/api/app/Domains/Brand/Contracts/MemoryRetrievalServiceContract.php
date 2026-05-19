<?php

declare(strict_types=1);

namespace App\Domains\Brand\Contracts;

use App\Domains\AI\Data\MemoryContext;

/**
 * Retrieves brand memory chunks for RAG injection (implementation expands later).
 */
interface MemoryRetrievalServiceContract
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
    ): MemoryContext;
}

<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\AI\Data\MemoryChunkReference;
use App\Domains\AI\Data\MemoryContext;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * Enriched brand memory bundle for prompt injection and analytics export.
 */
final class BrandMemoryEnrichmentDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $promptVariables
     * @param  list<MemoryChunkReference>  $memoryChunks
     * @param  array<string, mixed>  $analyticsPayload
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly int $memoryVersion,
        public readonly ?BrandProfileDto $profile,
        public readonly array $promptVariables,
        public readonly array $memoryChunks,
        public readonly array $analyticsPayload,
    ) {
    }

    public function toMemoryContext(): MemoryContext
    {
        return new MemoryContext(
            workspaceId: $this->workspaceId,
            memoryVersion: $this->memoryVersion,
            chunks: $this->memoryChunks,
        );
    }

    public function isEmpty(): bool
    {
        return $this->profile === null && $this->memoryChunks === [];
    }
}

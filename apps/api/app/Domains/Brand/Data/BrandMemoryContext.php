<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\AI\Data\MemoryChunkReference;
use App\Domains\AI\Data\MemoryContext;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * Agent-ready brand memory bundle — compact prompts + analytics + optional gateway chunks.
 */
final class BrandMemoryContext extends DataTransferObject
{
    /**
     * @param  list<string>  $bannedPhrases
     * @param  list<string>  $preferredCtas
     * @param  list<string>  $preferredHookPatterns
     * @param  list<MemoryChunkReference>  $selectedChunks
     * @param  array<string, mixed>  $promptVariables
     * @param  array<string, mixed>  $personalizationMeta
     * @param  array<string, mixed>  $styleSignals
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly int $memoryVersion,
        public readonly ?string $profileId,
        public readonly string $compactBrandSection,
        public readonly array $bannedPhrases,
        public readonly array $preferredCtas,
        public readonly array $preferredHookPatterns,
        public readonly array $selectedChunks,
        public readonly array $promptVariables,
        public readonly array $personalizationMeta,
        public readonly array $styleSignals,
        public readonly bool $usedFallback = false,
        public readonly ?BrandProfileDto $profile = null,
    ) {
    }

    public function hasBrandMemory(): bool
    {
        return $this->compactBrandSection !== '' || $this->selectedChunks !== [];
    }

    /**
     * Memory for LlmGateway assembler — omitted when compact section is in the user prompt (anti-bloat).
     */
    public function memoryContextForGateway(): ?MemoryContext
    {
        if ($this->compactBrandSection !== '') {
            return null;
        }

        if ($this->selectedChunks === []) {
            return null;
        }

        return new MemoryContext(
            workspaceId: $this->workspaceId,
            memoryVersion: $this->memoryVersion,
            chunks: $this->selectedChunks,
        );
    }

    /**
     * Full context for persistence / analytics (chunk ids always tracked).
     */
    public function memoryContextForPersistence(): MemoryContext
    {
        return new MemoryContext(
            workspaceId: $this->workspaceId,
            memoryVersion: $this->memoryVersion,
            chunks: $this->selectedChunks,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAnalyticsPayload(): array
    {
        return array_merge($this->personalizationMeta, [
            'workspace_id' => $this->workspaceId,
            'profile_id' => $this->profileId,
            'memory_version' => $this->memoryVersion,
            'used_fallback' => $this->usedFallback,
            'chunk_ids' => array_map(static fn (MemoryChunkReference $c) => $c->id, $this->selectedChunks),
            'compact_section_chars' => strlen($this->compactBrandSection),
            'banned_phrase_count' => count($this->bannedPhrases),
        ]);
    }
}

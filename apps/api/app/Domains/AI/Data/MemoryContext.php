<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Brand memory bundle injected into LLM requests.
 */
final class MemoryContext extends DataTransferObject
{
    /**
     * @param  list<MemoryChunkReference>  $chunks
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly int $memoryVersion,
        public readonly array $chunks,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->chunks === [];
    }

    /**
     * Format chunks for system prompt injection.
     */
    public function toSystemPromptSection(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        $preamble = (string) config('ai.memory.system_preamble', '');
        $lines = [$preamble, '', '---', ''];

        foreach ($this->chunks as $chunk) {
            $lines[] = sprintf(
                '%s (type: %s)%s',
                $chunk->citationTag(),
                $chunk->type,
                $chunk->score !== null ? ' score: '.round($chunk->score, 4) : ''
            );
            $lines[] = $chunk->content;
            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }
}

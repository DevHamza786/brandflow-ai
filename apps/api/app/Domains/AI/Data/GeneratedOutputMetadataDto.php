<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Analytics, observability, and future fine-tuning / vector memory fields.
 */
final class GeneratedOutputMetadataDto extends DataTransferObject
{
    /**
     * @param  list<string>  $memoryChunkIds
     * @param  array<string, mixed>  $tokenUsage
     * @param  array<string, mixed>  $orchestration
     * @param  array<string, mixed>  $fineTuning
     * @param  array<string, mixed>  $extras
     */
    public function __construct(
        public readonly ?string $traceId = null,
        public readonly array $memoryChunkIds = [],
        public readonly ?string $embeddingId = null,
        public readonly ?int $memoryVersion = null,
        public readonly array $tokenUsage = [],
        public readonly array $orchestration = [],
        public readonly array $fineTuning = [],
        public readonly array $extras = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): static
    {
        $known = [
            'trace_id',
            'memory_chunk_ids',
            'embedding_id',
            'memory_version',
            'token_usage',
            'orchestration',
            'fine_tuning',
        ];

        $extras = array_diff_key($payload, array_flip($known));

        return new self(
            traceId: isset($payload['trace_id']) ? (string) $payload['trace_id'] : null,
            memoryChunkIds: is_array($payload['memory_chunk_ids'] ?? null)
                ? array_values($payload['memory_chunk_ids'])
                : [],
            embeddingId: isset($payload['embedding_id']) ? (string) $payload['embedding_id'] : null,
            memoryVersion: isset($payload['memory_version']) ? (int) $payload['memory_version'] : null,
            tokenUsage: is_array($payload['token_usage'] ?? null) ? $payload['token_usage'] : [],
            orchestration: is_array($payload['orchestration'] ?? null) ? $payload['orchestration'] : [],
            fineTuning: is_array($payload['fine_tuning'] ?? null) ? $payload['fine_tuning'] : [],
            extras: $extras,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorageArray(): array
    {
        return array_filter(
            array_merge(
                [
                    'trace_id' => $this->traceId,
                    'memory_chunk_ids' => $this->memoryChunkIds,
                    'embedding_id' => $this->embeddingId,
                    'memory_version' => $this->memoryVersion,
                    'token_usage' => $this->tokenUsage,
                    'orchestration' => $this->orchestration,
                    'fine_tuning' => $this->fineTuning,
                ],
                $this->extras,
            ),
            static fn ($value) => $value !== null && $value !== [] && $value !== '',
        );
    }
}

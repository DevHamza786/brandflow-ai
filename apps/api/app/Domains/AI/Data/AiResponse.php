<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Primary completion response DTO for orchestration and agents.
 */
final class AiResponse extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $structured  Decoded JSON when structured output requested
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $content,
        public readonly string $provider,
        public readonly string $model,
        public readonly TokenUsage $tokenUsage,
        public readonly ?array $structured = null,
        public readonly ?string $traceId = null,
        public readonly array $metadata = [],
    ) {
    }

    public function toLlmResponse(): LlmResponse
    {
        return new LlmResponse(
            content: $this->content,
            provider: $this->provider,
            model: $this->model,
            tokenUsage: $this->tokenUsage,
            structured: $this->structured,
            traceId: $this->traceId,
            metadata: $this->metadata,
        );
    }
}

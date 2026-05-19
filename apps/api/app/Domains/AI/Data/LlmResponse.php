<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Completion response (backward-compatible alias shape for LlmGateway contract).
 */
final class LlmResponse extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $structured
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

    public function toAiResponse(): AiResponse
    {
        return new AiResponse(
            content: $this->content,
            provider: $this->provider,
            model: $this->model,
            tokenUsage: $this->tokenUsage,
            structured: $this->structured,
            traceId: $this->traceId,
            metadata: $this->metadata,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'provider' => $this->provider,
            'model' => $this->model,
            'usage' => $this->tokenUsage->toArray(),
            'structured' => $this->structured,
            'trace_id' => $this->traceId,
            'metadata' => $this->metadata,
        ];
    }
}

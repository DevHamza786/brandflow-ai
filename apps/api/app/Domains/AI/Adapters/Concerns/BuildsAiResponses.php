<?php

declare(strict_types=1);

namespace App\Domains\AI\Adapters\Concerns;

use App\Domains\AI\Data\AiResponse;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Data\StructuredOutputConfig;
use App\Domains\AI\Data\TokenUsage;
use App\Domains\AI\Support\StructuredOutputDecoder;

trait BuildsAiResponses
{
    private function decodeStructured(StructuredOutputDecoder $decoder, string $content, ?StructuredOutputConfig $config): ?array
    {
        return $decoder->decode($content, $config);
    }

    private function buildAiResponse(
        LlmRequest $request,
        string $provider,
        string $content,
        TokenUsage $usage,
        ?array $structured = null,
        array $metadata = [],
    ): AiResponse {
        return new AiResponse(
            content: $content,
            provider: $provider,
            model: $request->model,
            tokenUsage: $usage,
            structured: $structured,
            traceId: $request->traceId,
            metadata: array_merge($request->metadata, $metadata),
        );
    }
}

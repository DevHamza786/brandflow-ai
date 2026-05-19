<?php

declare(strict_types=1);

namespace App\Domains\AI\Adapters;

use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\AI\Data\AiResponse;
use App\Domains\AI\Data\EmbedRequest;
use App\Domains\AI\Data\EmbedResponse;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Data\TokenUsage;
use Generator;
use RuntimeException;

/**
 * No-op gateway for tests / local without API keys (AI_USE_NULL_GATEWAY=true).
 */
final class NullLlmGateway implements LlmGateway
{
    public function complete(LlmRequest $request): AiResponse
    {
        throw new RuntimeException('NullLlmGateway is active. Set AI_USE_NULL_GATEWAY=false and configure provider API keys.');
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        throw new RuntimeException('NullLlmGateway is active.');
    }

    public function stream(LlmRequest $request): Generator
    {
        throw new RuntimeException('NullLlmGateway is active.');

        yield from [];
    }
}

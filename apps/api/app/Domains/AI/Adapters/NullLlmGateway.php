<?php

declare(strict_types=1);

namespace App\Domains\AI\Adapters;

use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\AI\Data\EmbedRequest;
use App\Domains\AI\Data\EmbedResponse;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Data\LlmResponse;
use Generator;
use RuntimeException;

/**
 * Placeholder gateway until provider adapters are implemented.
 */
final class NullLlmGateway implements LlmGateway
{
    public function complete(LlmRequest $request): LlmResponse
    {
        throw new RuntimeException('LlmGateway::complete is not configured. Bind a concrete adapter in AIServiceProvider.');
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        throw new RuntimeException('LlmGateway::embed is not configured. Bind a concrete adapter in AIServiceProvider.');
    }

    public function stream(LlmRequest $request): Generator
    {
        throw new RuntimeException('LlmGateway::stream is not configured. Bind a concrete adapter in AIServiceProvider.');
    }
}

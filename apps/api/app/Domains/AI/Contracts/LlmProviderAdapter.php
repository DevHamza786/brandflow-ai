<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Data\AiResponse;
use App\Domains\AI\Data\EmbedRequest;
use App\Domains\AI\Data\EmbedResponse;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Enums\LlmProvider;
use Generator;

/**
 * Low-level vendor adapter — all HTTP calls live in implementations.
 */
interface LlmProviderAdapter
{
    public function provider(): LlmProvider;

    public function isConfigured(): bool;

    public function complete(LlmRequest $request): AiResponse;

    public function embed(EmbedRequest $request): EmbedResponse;

    /**
     * @return Generator<string>
     */
    public function stream(LlmRequest $request): Generator;
}

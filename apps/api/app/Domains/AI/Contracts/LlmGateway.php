<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Data\EmbedRequest;
use App\Domains\AI\Data\EmbedResponse;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Data\LlmResponse;
use Generator;

/**
 * Unified LLM provider interface for all AI operations.
 *
 * @see docs/PROJECT_ARCHITECTURE.md §5.1 Provider Abstraction
 */
interface LlmGateway
{
    public function complete(LlmRequest $request): LlmResponse;

    public function embed(EmbedRequest $request): EmbedResponse;

    /**
     * @return Generator<string>
     */
    public function stream(LlmRequest $request): Generator;
}

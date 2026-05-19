<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Data\AiResponse;
use App\Domains\AI\Data\EmbedRequest;
use App\Domains\AI\Data\EmbedResponse;
use App\Domains\AI\Data\LlmRequest;
use Generator;

/**
 * Unified LLM gateway — sole entry point for application / agent code.
 *
 * @see docs/PROJECT_ARCHITECTURE.md §5.1 Provider Abstraction
 */
interface LlmGateway
{
    public function complete(LlmRequest $request): AiResponse;

    public function embed(EmbedRequest $request): EmbedResponse;

    /**
     * @return Generator<string>
     */
    public function stream(LlmRequest $request): Generator;
}

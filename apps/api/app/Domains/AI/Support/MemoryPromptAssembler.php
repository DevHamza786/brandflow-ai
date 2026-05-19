<?php

declare(strict_types=1);

namespace App\Domains\AI\Support;

use App\Domains\AI\Data\AiMessage;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Enums\AiMessageRole;

/**
 * Injects MemoryContext into message list as a system preamble.
 */
final class MemoryPromptAssembler
{
    /**
     * @return list<AiMessage>
     */
    public function assemble(LlmRequest $request): array
    {
        $memory = $request->memoryContext;

        if ($memory === null || $memory->isEmpty()) {
            return $request->messages;
        }

        $section = $memory->toSystemPromptSection();

        if ($section === '') {
            return $request->messages;
        }

        $memoryMessage = new AiMessage(
            role: AiMessageRole::System,
            content: $section,
        );

        $messages = $request->messages;

        if ($messages !== [] && $messages[0]->role === AiMessageRole::System) {
            $merged = new AiMessage(
                role: AiMessageRole::System,
                content: $messages[0]->content."\n\n".$section,
            );

            return [$merged, ...array_slice($messages, 1)];
        }

        return [$memoryMessage, ...$messages];
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\AI\Support;

use App\Domains\AI\Data\StructuredOutputConfig;
use App\Domains\AI\Exceptions\StructuredOutputException;

/**
 * Decodes and validates JSON structured outputs from LLM responses.
 */
final class StructuredOutputDecoder
{
    /**
     * @return array<string, mixed>|null
     */
    public function decode(string $content, ?StructuredOutputConfig $config): ?array
    {
        if ($config === null) {
            return null;
        }

        $trimmed = trim($content);

        if ($trimmed === '') {
            throw new StructuredOutputException('Structured output requested but response was empty.');
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new StructuredOutputException(
                'Failed to decode structured JSON output: '.$e->getMessage(),
                ['content_preview' => mb_substr($trimmed, 0, 500)],
                previous: $e
            );
        }

        return $decoded;
    }
}

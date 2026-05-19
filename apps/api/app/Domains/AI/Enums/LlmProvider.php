<?php

declare(strict_types=1);

namespace App\Domains\AI\Enums;

enum LlmProvider: string
{
    case OpenAi = 'openai';
    case Gemini = 'gemini';

    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom(strtolower($value));
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom(strtolower($value))
            ?? throw new \InvalidArgumentException("Unknown LLM provider [{$value}].");
    }
}

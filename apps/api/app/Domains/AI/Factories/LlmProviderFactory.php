<?php

declare(strict_types=1);

namespace App\Domains\AI\Factories;

use App\Domains\AI\Adapters\GeminiAdapter;
use App\Domains\AI\Adapters\OpenAiAdapter;
use App\Domains\AI\Contracts\LlmProviderAdapter;
use App\Domains\AI\Contracts\LlmProviderFactoryContract;
use App\Domains\AI\Enums\LlmProvider;
use App\Domains\AI\Exceptions\ProviderNotConfiguredException;
final class LlmProviderFactory implements LlmProviderFactoryContract
{
    public function __construct(
        private readonly OpenAiAdapter $openAi,
        private readonly GeminiAdapter $gemini,
    ) {
    }

    public function make(LlmProvider|string $provider): LlmProviderAdapter
    {
        $enum = is_string($provider) ? LlmProvider::fromString($provider) : $provider;

        return match ($enum) {
            LlmProvider::OpenAi => $this->openAi,
            LlmProvider::Gemini => $this->gemini,
        };
    }

    public function makeDefault(): LlmProviderAdapter
    {
        return $this->make((string) config('ai.default_provider', 'openai'));
    }

    public function makeFallback(): LlmProviderAdapter
    {
        return $this->make((string) config('ai.fallback_provider', 'gemini'));
    }

    /**
     * @return list<LlmProviderAdapter>
     */
    public function configured(): array
    {
        return array_values(array_filter(
            [$this->openAi, $this->gemini],
            static fn (LlmProviderAdapter $adapter): bool => $adapter->isConfigured()
        ));
    }

    public function makeConfigured(LlmProvider|string $provider): LlmProviderAdapter
    {
        $adapter = $this->make($provider);

        if (! $adapter->isConfigured()) {
            throw new ProviderNotConfiguredException(
                "Provider [{$adapter->provider()->value}] is not configured."
            );
        }

        return $adapter;
    }
}

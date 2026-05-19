<?php

declare(strict_types=1);

namespace App\Domains\AI\Services;

use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\AI\Contracts\LlmProviderFactoryContract;
use App\Domains\AI\Data\AiResponse;
use App\Domains\AI\Data\EmbedRequest;
use App\Domains\AI\Data\EmbedResponse;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Enums\LlmProvider;
use App\Domains\AI\Exceptions\AiException;
use App\Domains\AI\Exceptions\ProviderNotConfiguredException;
use App\Domains\AI\Support\MemoryPromptAssembler;
use App\Domains\AI\Support\RetryExecutor;
use Generator;
use Illuminate\Support\Str;

/**
 * Orchestrating gateway: memory injection, retry, fallback, token tracking.
 */
final class LlmGatewayService implements LlmGateway
{
    public function __construct(
        private readonly LlmProviderFactoryContract $factory,
        private readonly MemoryPromptAssembler $memoryAssembler,
        private readonly RetryExecutor $retry,
    ) {
    }

    public function complete(LlmRequest $request): AiResponse
    {
        $request = $this->prepareRequest($request);

        return $this->retry->run(function () use ($request): AiResponse {
            try {
                return $this->factory->makeConfigured($request->provider)->complete($request);
            } catch (ProviderNotConfiguredException $e) {
                throw $e;
            } catch (AiException $e) {
                if ($this->shouldFallback($request->provider)) {
                    return $this->completeViaFallback($request);
                }

                throw $e;
            }
        });
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        return $this->retry->run(function () use ($request): EmbedResponse {
            return $this->factory->makeConfigured($request->provider)->embed($request);
        });
    }

    public function stream(LlmRequest $request): Generator
    {
        $request = $this->prepareRequest($request);
        $adapter = $this->factory->makeConfigured($request->provider);

        yield from $adapter->stream($request);
    }

    private function prepareRequest(LlmRequest $request): LlmRequest
    {
        $messages = $this->memoryAssembler->assemble($request);
        $traceId = $request->traceId ?? (string) Str::uuid();

        return new LlmRequest(
            workspaceId: $request->workspaceId,
            provider: $request->provider,
            model: $request->model,
            messages: $messages,
            structuredOutput: $request->structuredOutput,
            memoryContext: $request->memoryContext,
            maxTokens: $request->maxTokens,
            temperature: $request->temperature,
            traceId: $traceId,
            promptSlug: $request->promptSlug,
            promptVersion: $request->promptVersion,
            metadata: $request->metadata,
        );
    }

    private function shouldFallback(string $currentProvider): bool
    {
        if (! config('ai.enable_fallback', true)) {
            return false;
        }

        $fallback = (string) config('ai.fallback_provider', 'gemini');

        return strtolower($currentProvider) !== strtolower($fallback);
    }

    private function completeViaFallback(LlmRequest $request): AiResponse
    {
        $fallbackProvider = (string) config('ai.fallback_provider', 'gemini');
        $fallbackModel = $this->defaultModelFor($fallbackProvider);

        $fallbackRequest = new LlmRequest(
            workspaceId: $request->workspaceId,
            provider: $fallbackProvider,
            model: $fallbackModel,
            messages: $request->messages,
            structuredOutput: $request->structuredOutput,
            memoryContext: $request->memoryContext,
            maxTokens: $request->maxTokens,
            temperature: $request->temperature,
            traceId: $request->traceId,
            promptSlug: $request->promptSlug,
            promptVersion: $request->promptVersion,
            metadata: array_merge($request->metadata, [
                'fallback_from' => $request->provider,
                'fallback_model_original' => $request->model,
            ]),
        );

        return $this->factory->makeConfigured($fallbackProvider)->complete($fallbackRequest);
    }

    private function defaultModelFor(string $provider): string
    {
        return match (LlmProvider::tryFromString($provider)) {
            LlmProvider::Gemini => (string) config('ai.providers.gemini.default_model'),
            default => (string) config('ai.providers.openai.default_model'),
        };
    }
}

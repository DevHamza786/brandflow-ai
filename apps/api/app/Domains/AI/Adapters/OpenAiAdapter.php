<?php

declare(strict_types=1);

namespace App\Domains\AI\Adapters;

use App\Domains\AI\Adapters\Concerns\BuildsAiResponses;
use App\Domains\AI\Contracts\LlmProviderAdapter;
use App\Domains\AI\Data\AiResponse;
use App\Domains\AI\Data\EmbedRequest;
use App\Domains\AI\Data\EmbedResponse;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Enums\LlmProvider;
use App\Domains\AI\Exceptions\ProviderNotConfiguredException;
use App\Domains\AI\Support\ProviderHttpClient;
use App\Domains\AI\Support\StructuredOutputDecoder;
use Generator;
use Illuminate\Http\Client\ConnectionException;

/**
 * OpenAI Chat Completions + Embeddings API adapter.
 */
final class OpenAiAdapter implements LlmProviderAdapter
{
    use BuildsAiResponses;

    public function __construct(
        private readonly ProviderHttpClient $http,
        private readonly StructuredOutputDecoder $structuredDecoder,
    ) {
    }

    public function provider(): LlmProvider
    {
        return LlmProvider::OpenAi;
    }

    public function isConfigured(): bool
    {
        return filled($this->config('api_key'));
    }

    public function complete(LlmRequest $request): AiResponse
    {
        $this->ensureConfigured();

        $payload = [
            'model' => $request->model,
            'messages' => $request->toProviderMessages(),
        ];

        if ($request->maxTokens !== null) {
            $payload['max_tokens'] = $request->maxTokens;
        }

        if ($request->temperature !== null) {
            $payload['temperature'] = $request->temperature;
        }

        if ($request->structuredOutput !== null) {
            $payload['response_format'] = $this->mapStructuredOutput($request->structuredOutput);
        }

        try {
            $response = $this->request()
                ->withHeaders($this->traceHeaders($request->traceId))
                ->post('/chat/completions', $payload);
        } catch (ConnectionException $e) {
            throw $this->http->wrapConnectionException($e, $this->provider()->value);
        }

        $this->http->throwIfFailed($response, $this->provider()->value);

        $data = $response->json();
        $content = (string) ($data['choices'][0]['message']['content'] ?? '');
        $usage = TokenUsage::fromProviderUsage($data['usage'] ?? []);
        $structured = $this->decodeStructured($this->structuredDecoder, $content, $request->structuredOutput);

        return $this->buildAiResponse(
            request: $request,
            provider: $this->provider()->value,
            content: $content,
            usage: $usage,
            structured: $structured,
            metadata: ['openai_id' => $data['id'] ?? null],
        );
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        $this->ensureConfigured();

        try {
            $response = $this->request()
                ->withHeaders($this->traceHeaders($request->traceId))
                ->post('/embeddings', [
                    'model' => $request->model,
                    'input' => $request->input,
                ]);
        } catch (ConnectionException $e) {
            throw $this->http->wrapConnectionException($e, $this->provider()->value);
        }

        $this->http->throwIfFailed($response, $this->provider()->value);

        $data = $response->json();
        $vector = $data['data'][0]['embedding'] ?? [];

        return new EmbedResponse(
            vector: array_map('floatval', $vector),
            provider: $this->provider()->value,
            model: $request->model,
            tokenUsage: TokenUsage::fromProviderUsage($data['usage'] ?? []),
            traceId: $request->traceId,
        );
    }

    public function stream(LlmRequest $request): Generator
    {
        $this->ensureConfigured();

        $payload = [
            'model' => $request->model,
            'messages' => $request->toProviderMessages(),
            'stream' => true,
        ];

        if ($request->maxTokens !== null) {
            $payload['max_tokens'] = $request->maxTokens;
        }

        if ($request->temperature !== null) {
            $payload['temperature'] = $request->temperature;
        }

        try {
            $response = $this->request()
                ->withHeaders($this->traceHeaders($request->traceId))
                ->withOptions(['stream' => true])
                ->post('/chat/completions', $payload);
        } catch (ConnectionException $e) {
            throw $this->http->wrapConnectionException($e, $this->provider()->value);
        }

        $this->http->throwIfFailed($response, $this->provider()->value);

        $body = $response->toPsrResponse()->getBody();

        while (! $body->eof()) {
            $line = $this->readStreamLine($body);

            if ($line === '' || ! str_starts_with($line, 'data: ')) {
                continue;
            }

            $data = trim(substr($line, 6));

            if ($data === '[DONE]') {
                break;
            }

            $json = json_decode($data, true);

            if (! is_array($json)) {
                continue;
            }

            $delta = $json['choices'][0]['delta']['content'] ?? null;

            if (is_string($delta) && $delta !== '') {
                yield $delta;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapStructuredOutput(\App\Domains\AI\Data\StructuredOutputConfig $config): array
    {
        if ($config->type === 'json_schema' && $config->schema !== null) {
            return [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => $config->schemaName ?? 'response',
                    'strict' => $config->strict,
                    'schema' => $config->schema,
                ],
            ];
        }

        return ['type' => 'json_object'];
    }

    /**
     * @return array<string, string>
     */
    private function traceHeaders(?string $traceId): array
    {
        if ($traceId === null) {
            return [];
        }

        return [(string) config('ai.trace_id_header', 'X-PBOS-Trace-Id') => $traceId];
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new ProviderNotConfiguredException('OpenAI API key is not configured (OPENAI_API_KEY).');
        }
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        $request = $this->http->baseRequest(
            (string) $this->config('base_url'),
            (string) $this->config('api_key'),
            (int) $this->config('timeout', 120),
        );

        if ($org = $this->config('organization')) {
            $request = $request->withHeaders(['OpenAI-Organization' => $org]);
        }

        return $request;
    }

    private function config(string $key, mixed $default = null): mixed
    {
        return config("ai.providers.openai.{$key}", $default);
    }

  /**
     * @param  \Psr\Http\Message\StreamInterface  $body
     */
    private function readStreamLine($body): string
    {
        $buffer = '';

        while (! $body->eof()) {
            $char = $body->read(1);

            if ($char === '' || $char === "\n") {
                break;
            }

            $buffer .= $char;
        }

        return $buffer;
    }
}

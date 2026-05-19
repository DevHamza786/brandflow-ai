<?php

declare(strict_types=1);

namespace App\Domains\AI\Adapters;

use App\Domains\AI\Adapters\Concerns\BuildsAiResponses;
use App\Domains\AI\Contracts\LlmProviderAdapter;
use App\Domains\AI\Data\AiMessage;
use App\Domains\AI\Data\AiResponse;
use App\Domains\AI\Data\EmbedRequest;
use App\Domains\AI\Data\EmbedResponse;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Enums\AiMessageRole;
use App\Domains\AI\Enums\LlmProvider;
use App\Domains\AI\Exceptions\ProviderNotConfiguredException;
use App\Domains\AI\Support\ProviderHttpClient;
use App\Domains\AI\Support\StructuredOutputDecoder;
use Generator;
use Illuminate\Http\Client\ConnectionException;

/**
 * Google Gemini generateContent + embedContent API adapter.
 */
final class GeminiAdapter implements LlmProviderAdapter
{
    use BuildsAiResponses;

    public function __construct(
        private readonly ProviderHttpClient $http,
        private readonly StructuredOutputDecoder $structuredDecoder,
    ) {
    }

    public function provider(): LlmProvider
    {
        return LlmProvider::Gemini;
    }

    public function isConfigured(): bool
    {
        return filled($this->config('api_key'));
    }

    public function complete(LlmRequest $request): AiResponse
    {
        $this->ensureConfigured();

        $payload = [
            'contents' => $this->mapMessages($request->messages),
            'generationConfig' => $this->generationConfig($request),
        ];

        $model = $request->model;
        $url = sprintf('/models/%s:generateContent', $model);

        try {
            $response = $this->request()
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw $this->http->wrapConnectionException($e, $this->provider()->value);
        }

        $this->http->throwIfFailed($response, $this->provider()->value);

        $data = $response->json();
        $content = $this->extractText($data);
        $usage = TokenUsage::fromProviderUsage($this->mapUsage($data));
        $structured = $this->decodeStructured($this->structuredDecoder, $content, $request->structuredOutput);

        return $this->buildAiResponse(
            request: $request,
            provider: $this->provider()->value,
            content: $content,
            usage: $usage,
            structured: $structured,
            metadata: ['gemini_model' => $model],
        );
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        $this->ensureConfigured();

        $model = $request->model;
        $url = sprintf('/models/%s:embedContent', $model);

        try {
            $response = $this->request()
                ->post($url, [
                    'content' => [
                        'parts' => [['text' => $request->input]],
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw $this->http->wrapConnectionException($e, $this->provider()->value);
        }

        $this->http->throwIfFailed($response, $this->provider()->value);

        $data = $response->json();
        $values = $data['embedding']['values'] ?? [];

        return new EmbedResponse(
            vector: array_map('floatval', $values),
            provider: $this->provider()->value,
            model: $request->model,
            tokenUsage: new \App\Domains\AI\Data\TokenUsage(),
            traceId: $request->traceId,
        );
    }

    public function stream(LlmRequest $request): Generator
    {
        $this->ensureConfigured();

        $model = $request->model;
        $url = sprintf('/models/%s:streamGenerateContent', $model);

        $payload = [
            'contents' => $this->mapMessages($request->messages),
            'generationConfig' => $this->generationConfig($request),
        ];

        try {
            $response = $this->request()
                ->withOptions(['stream' => true])
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw $this->http->wrapConnectionException($e, $this->provider()->value);
        }

        $this->http->throwIfFailed($response, $this->provider()->value);

        $body = $response->body();

        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);

            if ($line === '' || $line === '[' || $line === ']' || $line === ',') {
                continue;
            }

            $line = rtrim($line, ',');

            $json = json_decode($line, true);

            if (! is_array($json)) {
                continue;
            }

            $text = $this->extractText($json);

            if ($text !== '') {
                yield $text;
            }
        }
    }

    /**
     * @param  list<AiMessage>  $messages
     * @return list<array<string, mixed>>
     */
    private function mapMessages(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            $role = match ($message->role) {
                AiMessageRole::Assistant => 'model',
                AiMessageRole::System, AiMessageRole::User => 'user',
            };

            if ($message->role === AiMessageRole::System) {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => '[SYSTEM] '.$message->content]],
                ];

                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message->content]],
            ];
        }

        return $contents;
    }

    /**
     * @return array<string, mixed>
     */
    private function generationConfig(LlmRequest $request): array
    {
        $config = [];

        if ($request->maxTokens !== null) {
            $config['maxOutputTokens'] = $request->maxTokens;
        }

        if ($request->temperature !== null) {
            $config['temperature'] = $request->temperature;
        }

        if ($request->structuredOutput !== null) {
            $config['responseMimeType'] = 'application/json';
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extractText(array $data): string
    {
        $parts = $data['candidates'][0]['content']['parts'] ?? [];
        $texts = [];

        foreach ($parts as $part) {
            if (isset($part['text'])) {
                $texts[] = $part['text'];
            }
        }

        return implode('', $texts);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, int>
     */
    private function mapUsage(array $data): array
    {
        $meta = $data['usageMetadata'] ?? [];

        return [
            'prompt_tokens' => (int) ($meta['promptTokenCount'] ?? 0),
            'completion_tokens' => (int) ($meta['candidatesTokenCount'] ?? 0),
            'total_tokens' => (int) ($meta['totalTokenCount'] ?? 0),
        ];
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new ProviderNotConfiguredException('Gemini API key is not configured (GEMINI_API_KEY).');
        }
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        return \Illuminate\Support\Facades\Http::baseUrl(rtrim((string) $this->config('base_url'), '/'))
            ->timeout((int) $this->config('timeout', 120))
            ->acceptJson()
            ->withQueryParameters(['key' => (string) $this->config('api_key')]);
    }

    private function config(string $key, mixed $default = null): mixed
    {
        return config("ai.providers.gemini.{$key}", $default);
    }
}

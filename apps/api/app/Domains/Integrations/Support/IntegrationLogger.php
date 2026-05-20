<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Structured integration logging — analytics- and observability-ready.
 */
final class IntegrationLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $event, array $context = []): void
    {
        Log::info('integration.'.$event, $this->enrich($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function warning(string $event, array $context = []): void
    {
        Log::warning('integration.'.$event, $this->enrich($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function error(string $event, array $context = [], ?\Throwable $e = null): void
    {
        $payload = $this->enrich($context);
        if ($e !== null) {
            $payload['exception'] = $e->getMessage();
            $payload['exception_class'] = $e::class;
        }

        Log::error('integration.'.$event, $payload);
    }

    public function traceId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function enrich(array $context): array
    {
        return array_merge([
            'trace_id' => $context['trace_id'] ?? $this->traceId(),
            'ts' => now()->toIso8601String(),
        ], $context);
    }
}

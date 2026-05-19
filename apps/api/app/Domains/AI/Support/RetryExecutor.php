<?php

declare(strict_types=1);

namespace App\Domains\AI\Support;

use App\Domains\AI\Exceptions\ProviderRateLimitException;
use App\Domains\AI\Exceptions\ProviderTimeoutException;
use Closure;
use Throwable;

/**
 * Gateway-level retry with configurable backoff.
 */
final class RetryExecutor
{
    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function run(Closure $callback, ?int $maxAttempts = null): mixed
    {
        $maxAttempts ??= (int) config('ai.retry.max_attempts', 3);
        $backoffMs = config('ai.retry.backoff_ms', [500, 2000, 5000]);
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            try {
                return $callback();
            } catch (Throwable $e) {
                $lastException = $e;

                if (! $this->shouldRetry($e) || $attempt >= $maxAttempts - 1) {
                    throw $e;
                }

                $delayMs = $backoffMs[$attempt] ?? end($backoffMs);

                if ($e instanceof ProviderRateLimitException && $e->retryAfterSeconds !== null) {
                    $delayMs = max($delayMs, $e->retryAfterSeconds * 1000);
                }

                usleep((int) $delayMs * 1000);
                $attempt++;
            }
        }

        throw $lastException ?? new \RuntimeException('Retry exhausted without exception.');
    }

    private function shouldRetry(Throwable $e): bool
    {
        if ($e instanceof ProviderRateLimitException || $e instanceof ProviderTimeoutException) {
            return true;
        }

        $retryOn = config('ai.retry.retry_on', []);

        if ($e instanceof \App\Domains\AI\Exceptions\ProviderException) {
            $code = $e->getCode();

            return $code >= 500 && in_array('server_error', $retryOn, true);
        }

        return false;
    }
}

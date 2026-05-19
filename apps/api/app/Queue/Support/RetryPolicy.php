<?php

declare(strict_types=1);

namespace App\Queue\Support;

/**
 * Resolved retry / timeout policy for a queue.
 */
final readonly class RetryPolicy
{
    /**
     * @param  array<int, int>  $backoff
     */
    public function __construct(
        public int $tries,
        public array $backoff,
        public int $timeout,
    ) {
    }
}

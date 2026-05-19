<?php

declare(strict_types=1);

namespace App\Queue\Support;

use App\Queue\Enums\QueueName;

/**
 * Resolves retry policies from config/queues.php.
 */
final class RetryPolicyResolver
{
    public function forQueue(string $queue): RetryPolicy
    {
        $retry = config("queues.retry.{$queue}", config('queues.retry.default', []));
        $timeout = (int) config("queues.timeouts.{$queue}", 60);

        return new RetryPolicy(
            tries: (int) ($retry['tries'] ?? 3),
            backoff: array_map('intval', $retry['backoff'] ?? [10, 60, 300]),
            timeout: $timeout,
        );
    }

    public function forQueueName(QueueName $queue): RetryPolicy
    {
        return $this->forQueue($queue->value);
    }
}

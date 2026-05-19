<?php

declare(strict_types=1);

namespace App\Domains\AI\Exceptions;

final class ProviderRateLimitException extends ProviderException
{
    public function __construct(
        string $message,
        public readonly ?int $retryAfterSeconds = null,
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $context, 429, $previous);
    }
}

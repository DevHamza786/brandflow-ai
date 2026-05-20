<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Exceptions;

use RuntimeException;

/**
 * Caller should not retry the queue job (4xx / policy).
 */
final class UnretryablePublishingException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        public readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

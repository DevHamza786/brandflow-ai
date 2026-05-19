<?php

declare(strict_types=1);

namespace App\Domains\AI\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for the AI domain.
 */
class AiException extends Exception
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        public readonly array $context = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Agents\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Structured output from a completed agent run.
 */
final class AgentResult extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $output
     */
    public function __construct(
        public readonly array $output = [],
        public readonly ?string $summary = null,
        public readonly ?string $traceId = null,
    ) {
    }
}

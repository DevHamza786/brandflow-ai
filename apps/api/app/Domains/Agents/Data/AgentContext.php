<?php

declare(strict_types=1);

namespace App\Domains\Agents\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Immutable input context for an agent run.
 */
final class AgentContext extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $agentRunId,
        public readonly string $slug,
        public readonly array $input = [],
        public readonly array $options = [],
    ) {
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->input[$key] ?? $default;
    }

    public function option(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }
}

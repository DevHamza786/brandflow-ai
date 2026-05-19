<?php

declare(strict_types=1);

namespace App\Domains\Agents\Jobs;

use App\Domains\Shared\Jobs\BaseQueueJob;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Dispatches execution of a registered agent by slug.
 *
 * @see docs/AGENTS.md §7 Queue Rules
 */
final class RunAgentJob extends BaseQueueJob implements ShouldBeUnique
{
    public int $timeout;

    public function __construct(
        string $workspaceId,
        public readonly string $agentRunId,
        public readonly string $slug,
    ) {
        parent::__construct($workspaceId);

        $this->timeout = (int) config("agents.agents.{$slug}.timeout", 120);
    }

    public function uniqueId(): string
    {
        return $this->agentRunId;
    }

    public function queueName(): string
    {
        return (string) config("agents.agents.{$this->slug}.queue", 'ai');
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            ...parent::tags(),
            'agent:'.$this->slug,
            'run:'.$this->agentRunId,
        ];
    }

    public function handle(): void
    {
        // AgentRunner::run($this->slug, $this->agentRunId) — implemented in a later iteration.
    }
}

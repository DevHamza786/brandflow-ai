<?php

declare(strict_types=1);

namespace App\Domains\Agents\Jobs;

use App\Domains\Shared\Jobs\BaseQueueJob;
use App\Queue\Enums\QueueName;
use App\Queue\Support\JobTagger;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Dispatches execution of a registered agent by slug.
 *
 * @see docs/AGENTS.md §7 Queue Rules
 */
final class RunAgentJob extends BaseQueueJob implements ShouldBeUnique
{
    public function __construct(
        string $workspaceId,
        public readonly string $agentRunId,
        public readonly string $slug,
    ) {
        parent::__construct($workspaceId);
    }

    public function uniqueId(): string
    {
        return $this->agentRunId;
    }

    public function queueName(): string
    {
        $queue = (string) config("agents.agents.{$this->slug}.queue", QueueName::Ai->value);

        // Map legacy config value `scrape` → `scraping`.
        return $queue === 'scrape' ? QueueName::Scraping->value : $queue;
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return JobTagger::merge(
            parent::tags(),
            JobTagger::agent($this->slug),
            JobTagger::agentRun($this->agentRunId),
        );
    }

    public function handle(): void
    {
        // AgentRunner::run($this->slug, $this->agentRunId) — implemented in a later iteration.
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Agents\Jobs;

use App\Domains\Agents\Services\AgentRunner;
use App\Domains\Shared\Jobs\BaseQueueJob;
use App\Queue\Enums\QueueName;
use App\Queue\Support\JobTagger;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Queue entry point for HookAgent (ai queue).
 */
final class RunHookAgentJob extends BaseQueueJob implements ShouldBeUnique
{
    public int $timeout = 120;

    public function __construct(
        string $workspaceId,
        public readonly string $agentRunId,
    ) {
        parent::__construct($workspaceId);

        $this->timeout = (int) config('agents.agents.hook.timeout', 120);
    }

    public function uniqueId(): string
    {
        return $this->agentRunId;
    }

    public function queueName(): string
    {
        return QueueName::Ai->value;
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return JobTagger::merge(
            parent::tags(),
            JobTagger::agent('hook'),
            JobTagger::agentRun($this->agentRunId),
        );
    }

    public function handle(AgentRunner $runner): void
    {
        $runner->run('hook', $this->workspaceId, $this->agentRunId);
    }

    public function failed(?Throwable $exception): void
    {
        parent::failed($exception);

        Log::error('hook.agent.job_failed', [
            'workspace_id' => $this->workspaceId,
            'agent_run_id' => $this->agentRunId,
            'message' => $exception?->getMessage(),
        ]);
    }
}

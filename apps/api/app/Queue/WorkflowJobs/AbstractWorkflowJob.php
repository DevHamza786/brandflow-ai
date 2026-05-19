<?php

declare(strict_types=1);

namespace App\Queue\WorkflowJobs;

use App\Queue\Enums\QueueName;
use App\Queue\Jobs\AbstractQueueJob;
use App\Queue\Middleware\TrackWorkflowExecution;
use App\Queue\Support\JobTagger;

/**
 * Base job for workflow DAG orchestration (runs on workflows queue).
 */
abstract class AbstractWorkflowJob extends AbstractQueueJob
{
    public function __construct(
        string $workspaceId,
        public readonly string $workflowRunId,
        public readonly ?string $workflowStepId = null,
    ) {
        parent::__construct($workspaceId);
    }

    public function queueName(): string
    {
        return QueueName::Workflows->value;
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            ...parent::middleware(),
            new TrackWorkflowExecution,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        $tags = JobTagger::merge(
            parent::tags(),
            JobTagger::workflowRun($this->workflowRunId),
        );

        if ($this->workflowStepId !== null) {
            $tags[] = JobTagger::workflowStep($this->workflowStepId);
        }

        return $tags;
    }
}

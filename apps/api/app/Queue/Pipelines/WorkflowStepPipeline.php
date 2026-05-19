<?php

declare(strict_types=1);

namespace App\Queue\Pipelines;

use Illuminate\Pipeline\Pipeline;

/**
 * Pipeline for workflow step execution stages (validate → execute → persist).
 *
 * Register pipes in QueueServiceProvider when business steps are implemented.
 */
final class WorkflowStepPipeline
{
    /**
     * @param  array<int, class-string>  $pipes
     */
    public function __construct(
        private readonly array $pipes = [],
    ) {
    }

    public function process(WorkflowStepContext $context): WorkflowStepContext
    {
        if ($this->pipes === []) {
            return $context;
        }

        return app(Pipeline::class)
            ->send($context)
            ->through($this->pipes)
            ->thenReturn();
    }
}

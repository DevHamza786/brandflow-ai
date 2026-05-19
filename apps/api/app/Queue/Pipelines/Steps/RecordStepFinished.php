<?php

declare(strict_types=1);

namespace App\Queue\Pipelines\Steps;

use App\Queue\Pipelines\WorkflowStepContext;
use Closure;

/**
 * Pipeline stage: placeholder for post-step persistence (implemented later).
 */
final class RecordStepFinished
{
    public function handle(WorkflowStepContext $context, Closure $next): WorkflowStepContext
    {
        return $next($context);
    }
}

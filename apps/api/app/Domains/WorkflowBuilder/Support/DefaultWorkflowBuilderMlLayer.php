<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Support;

use App\Domains\WorkflowBuilder\Contracts\WorkflowBuilderMlCompatibilityLayerContract;

final class DefaultWorkflowBuilderMlLayer implements WorkflowBuilderMlCompatibilityLayerContract
{
    public function afterExecution(array $mlState, array $executionMetrics): array
    {
        if (! (bool) config('workflow_builder.ml.enabled', false)) {
            return $mlState;
        }

        $mlState['last_execution'] = $executionMetrics;

        return $mlState;
    }
}

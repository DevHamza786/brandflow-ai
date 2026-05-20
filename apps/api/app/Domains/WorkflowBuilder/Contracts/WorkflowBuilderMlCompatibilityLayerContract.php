<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Contracts;

interface WorkflowBuilderMlCompatibilityLayerContract
{
    /**
     * @param  array<string, mixed>  $mlState
     * @param  array<string, mixed>  $executionMetrics
     * @return array<string, mixed>
     */
    public function afterExecution(array $mlState, array $executionMetrics): array;
}

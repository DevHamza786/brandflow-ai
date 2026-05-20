<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Services;

use Illuminate\Support\Facades\Log;

final class WorkflowExecutionLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, array $context = []): void
    {
        Log::info('workflow_builder.'.$message, $context);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use Illuminate\Support\Facades\Log;

final class ExperimentExecutionLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, array $context = []): void
    {
        Log::info('experimentation.'.$message, $context);
    }
}

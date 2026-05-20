<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use Illuminate\Support\Facades\Log;

final class OptimizationExecutionLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $event, array $context = []): void
    {
        Log::info('optimization.'.$event, array_merge([
            'ts' => now()->toIso8601String(),
        ], $context));
    }
}

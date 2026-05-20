<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use Illuminate\Support\Facades\Log;

final class CoordinationExecutionLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, array $context = []): void
    {
        Log::info('coordination.'.$message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function warning(string $message, array $context = []): void
    {
        Log::warning('coordination.'.$message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function error(string $message, array $context = []): void
    {
        Log::error('coordination.'.$message, $context);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Services;

use App\Domains\Schedule\Enums\ScheduleExecutionPhase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Persisted orchestration spine + structured logs / analytics ingestion later.
 */
final class ScheduleExecutionLogger
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function record(
        string $workspaceId,
        ?string $scheduledPostId,
        ScheduleExecutionPhase $phase,
        string $traceId,
        array $payload = [],
    ): void {
        Log::info('schedule.execution', [
            'workspace_id' => $workspaceId,
            'scheduled_post_id' => $scheduledPostId,
            'phase' => $phase->value,
            'trace_id' => $traceId,
            'payload' => $payload,
        ]);

        try {
            DB::table('schedule_execution_events')->insert([
                'id' => (string) Str::uuid(),
                'workspace_id' => $workspaceId,
                'scheduled_post_id' => $scheduledPostId,
                'phase' => $phase->value,
                'trace_id' => $traceId,
                'payload' => array_merge(['ts' => now()->toIso8601String()], $payload),
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::debug('schedule.execution_persist_failed', [
                'workspace_id' => $workspaceId,
                'scheduled_post_id' => $scheduledPostId,
                'phase' => $phase->value,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}

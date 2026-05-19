<?php

declare(strict_types=1);

namespace App\Queue\Support;

/**
 * Helpers for Horizon job tags and Redis observability keys.
 */
final class JobTagger
{
    public static function workspace(string $workspaceId): string
    {
        return 'workspace:'.$workspaceId;
    }

    public static function agent(string $slug): string
    {
        return 'agent:'.$slug;
    }

    public static function agentRun(string $runId): string
    {
        return 'run:'.$runId;
    }

    public static function workflow(string $workflowId): string
    {
        return 'workflow:'.$workflowId;
    }

    public static function workflowRun(string $runId): string
    {
        return 'workflow_run:'.$runId;
    }

    public static function workflowStep(string $stepId): string
    {
        return 'step:'.$stepId;
    }

    public static function queue(string $queue): string
    {
        return 'queue:'.$queue;
    }

    /**
     * @param  array<int, string>  $tags
     * @return array<int, string>
     */
    public static function merge(array $tags, string ...$additional): array
    {
        return array_values(array_unique([...$tags, ...$additional]));
    }
}

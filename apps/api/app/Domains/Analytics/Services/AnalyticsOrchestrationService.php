<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Enums\AnalyticsEventType;

/**
 * Coordinates analytics writes for workflow / publish lifecycle hooks.
 */
final class AnalyticsOrchestrationService
{
    public function __construct(
        private readonly AnalyticsEventIngestionService $ingestion,
        private readonly EngagementTrackingService $engagement,
        private readonly AnalyticsQueryService $query,
        private readonly AnalyticsExecutionLogger $logger,
    ) {
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function recordWorkflowSignal(
        string $workspaceId,
        string $workflowRunId,
        array $properties,
    ): void {
        $this->ingestion->ingest(
            workspaceId: $workspaceId,
            eventType: AnalyticsEventType::WorkflowSignal->value,
            entityType: 'workflow_run',
            entityId: $workflowRunId,
            properties: $properties,
            idempotencyKey: 'workflow:'.$workflowRunId.':'.($properties['phase'] ?? 'signal'),
        );
    }

    /**
     * @param  array<string, mixed>  $hookPayload  From HookScored event
     */
    public function recordHookScored(
        string $workspaceId,
        string $contentVersionId,
        string $agentRunId,
        string $hookScoreId,
        array $hookPayload,
        ?string $generatedOutputId = null,
    ): void {
        $this->ingestion->ingest(
            workspaceId: $workspaceId,
            eventType: AnalyticsEventType::HookScored->value,
            entityType: 'content_version',
            entityId: $contentVersionId,
            properties: array_merge($hookPayload, [
                'agent_run_id' => $agentRunId,
                'hook_score_id' => $hookScoreId,
                'generated_output_id' => $generatedOutputId,
            ]),
            idempotencyKey: 'hook_scored:'.$hookScoreId,
        );

        $this->logger->info('hook_scored.ingested', [
            'workspace_id' => $workspaceId,
            'hook_score_id' => $hookScoreId,
        ]);
    }

    public function query(): AnalyticsQueryService
    {
        return $this->query;
    }

    public function engagement(): EngagementTrackingService
    {
        return $this->engagement;
    }
}

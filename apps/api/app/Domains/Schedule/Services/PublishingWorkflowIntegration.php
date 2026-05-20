<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Services;

use App\Domains\Schedule\Actions\PublishToLinkedInAction;
use App\Domains\Schedule\Data\CreateScheduledPostDto;
use App\Domains\Schedule\Data\ScheduledPostDto;
use Carbon\CarbonInterface;

/**
 * Entry point for workflow runners to enqueue a publish without importing HTTP/provider code.
 */
final class PublishingWorkflowIntegration
{
    public function __construct(
        private readonly PublishToLinkedInAction $publishToLinkedIn,
    ) {
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function queueAfterWorkflow(
        string $workspaceId,
        string $linkedinIntegrationId,
        string $generatedOutputId,
        CarbonInterface $scheduledFor,
        string $workflowRunId,
        array $metadata = [],
    ): ScheduledPostDto {
        return $this->publishToLinkedIn->execute(new CreateScheduledPostDto(
            workspaceId: $workspaceId,
            linkedinIntegrationId: $linkedinIntegrationId,
            scheduledFor: $scheduledFor,
            content: null,
            generatedOutputId: $generatedOutputId,
            workflowRunId: $workflowRunId,
            metadata: array_merge($metadata, [
                'source' => 'workflow',
            ]),
        ));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Schedule\Data\ScheduledPostDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ScheduledPostDto $resource
 */
final class ScheduledPostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'workspace_id' => $dto->workspaceId,
            'platform' => $dto->platform->value,
            'schedule_pattern' => $dto->pattern->value,
            'recurrence_rule' => $dto->recurrenceRule,
            'series_id' => $dto->seriesId,
            'workflow_run_id' => $dto->workflowRunId,
            'execution_id' => $dto->executionId,
            'last_dispatched_at' => $dto->lastDispatchedAt?->toIso8601String(),
            'orchestration_metadata' => $dto->orchestrationMetadata,
            'generated_output_id' => $dto->generatedOutputId,
            'linkedin_integration_id' => $dto->linkedinIntegrationId,
            'content_item_id' => $dto->contentItemId,
            'content_version_id' => $dto->contentVersionId,
            'status' => $dto->status->value,
            'scheduled_for' => $dto->scheduledFor?->toIso8601String(),
            'publish_at' => $dto->publishAt->toIso8601String(),
            'timezone' => $dto->timezone,
            'provider_post_id' => $dto->providerPostId,
            'linkedin_urn' => $dto->linkedinUrn,
            'published_at' => $dto->publishedAt?->toIso8601String(),
            'attempt_count' => $dto->attemptCount,
            'content' => $dto->content,
            'content_preview' => $this->preview($dto->content),
            'metadata' => $dto->metadata,
            'error_details' => $dto->errorDetails,
        ];
    }

    private function preview(?string $content, int $max = 220): ?string
    {
        if ($content === null || $content === '') {
            return null;
        }

        if (mb_strlen($content) <= $max) {
            return $content;
        }

        return mb_substr($content, 0, $max).'…';
    }
}

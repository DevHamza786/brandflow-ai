<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Support;

use App\Domains\Schedule\Data\ScheduledPostDto;
use App\Domains\Schedule\Enums\SchedulePattern;
use App\Domains\Schedule\Enums\SchedulePlatform;
use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Schedule\Models\ScheduledPost;
use Illuminate\Support\Carbon;

final class ScheduledPostNormalizer
{
    public function normalize(ScheduledPost $model): ScheduledPostDto
    {
        $publishAt = $model->publish_at ?? $model->scheduled_for ?? Carbon::now();
        $scheduledFor = $model->scheduled_for ?? $model->publish_at;
        $orchestration = is_array($model->orchestration_metadata)
            ? $model->orchestration_metadata : null;

        return new ScheduledPostDto(
            id: (string) $model->id,
            workspaceId: (string) $model->workspace_id,
            platform: SchedulePlatform::fromString((string) ($model->platform ?? SchedulePlatform::LinkedIn->value)),
            pattern: SchedulePattern::fromString((string) ($model->schedule_pattern ?? SchedulePattern::Once->value)),
            recurrenceRule: is_array($model->recurrence_rule) ? $model->recurrence_rule : null,
            seriesId: $model->series_id,
            linkedinIntegrationId: $model->linkedin_integration_id,
            generatedOutputId: $model->generated_output_id,
            contentItemId: $model->content_item_id,
            contentVersionId: $model->content_version_id,
            content: $model->content,
            publishAt: $publishAt,
            scheduledFor: $scheduledFor,
            timezone: (string) $model->timezone,
            status: ScheduledPostStatus::fromString((string) $model->status),
            workflowRunId: $model->workflow_run_id !== null ? (string) $model->workflow_run_id : null,
            executionId: $model->execution_id !== null ? (string) $model->execution_id : null,
            lastDispatchedAt: $model->last_dispatched_at,
            orchestrationMetadata: $orchestration,
            providerPostId: $model->provider_post_id,
            linkedinUrn: $model->linkedin_urn,
            publishedAt: $model->published_at,
            lastAttemptAt: $model->last_attempt_at,
            attemptCount: (int) $model->attempt_count,
            error: is_array($model->error) ? $model->error : null,
            errorDetails: is_array($model->error_details) ? $model->error_details : null,
            metadata: is_array($model->metadata) ? $model->metadata : [],
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}

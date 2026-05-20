<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Repositories;

use App\Domains\Schedule\Contracts\ScheduledPostRepositoryContract;
use App\Domains\Schedule\Data\CreateScheduledPostDto;
use App\Domains\Schedule\Data\ScheduledPostDto;
use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Schedule\Models\ScheduledPost;
use App\Domains\Schedule\Support\ScheduledPostNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class ScheduledPostRepository implements ScheduledPostRepositoryContract
{
    public function __construct(
        private readonly ScheduledPostNormalizer $normalizer,
    ) {
    }

    public function findById(string $workspaceId, string $id): ?ScheduledPostDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->first();

        return $model ? $this->normalizer->normalize($model) : null;
    }

    public function create(CreateScheduledPostDto $dto): ScheduledPostDto
    {
        $meta = $dto->metadata;
        if (! isset($meta['source'])) {
            $meta['source'] = 'api';
        }

        $model = ScheduledPost::query()->create([
            'workspace_id' => $dto->workspaceId,
            'platform' => $dto->platform->value,
            'schedule_pattern' => $dto->pattern->value,
            'recurrence_rule' => $dto->recurrenceRule,
            'series_id' => $dto->seriesId,
            'linkedin_integration_id' => $dto->linkedinIntegrationId,
            'generated_output_id' => $dto->generatedOutputId,
            'content_item_id' => $dto->contentItemId,
            'content_version_id' => $dto->contentVersionId,
            'content' => $dto->content,
            'publish_at' => $dto->scheduledFor,
            'scheduled_for' => $dto->scheduledFor,
            'timezone' => $dto->timezone,
            'status' => $dto->status->value,
            'workflow_run_id' => $dto->workflowRunId,
            'orchestration_metadata' => $dto->orchestrationMetadata === [] ? null : $dto->orchestrationMetadata,
            'metadata' => $meta,
            'attempt_count' => 0,
        ]);

        return $this->normalizer->normalize($model);
    }

    public function updateStatus(
        string $workspaceId,
        string $id,
        ScheduledPostStatus $status,
        ?array $errorDetails = null,
    ): ScheduledPostDto {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->status = $status->value;
        if ($errorDetails !== null) {
            $model->error_details = $errorDetails;
        }
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function markPublishing(string $workspaceId, string $id): ScheduledPostDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->status = ScheduledPostStatus::Publishing->value;
        $model->last_attempt_at = now();
        $model->attempt_count = (int) $model->attempt_count + 1;
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function markPublished(
        string $workspaceId,
        string $id,
        ?string $providerPostId,
        ?string $linkedinUrn,
        array $metadataPatch = [],
    ): ScheduledPostDto {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->status = ScheduledPostStatus::Published->value;
        $model->provider_post_id = $providerPostId;
        $model->linkedin_urn = $linkedinUrn ?? $providerPostId;
        $model->published_at = now();
        $model->error = null;
        $model->error_details = null;
        $model->metadata = array_merge(is_array($model->metadata) ? $model->metadata : [], $metadataPatch);
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function markFailed(
        string $workspaceId,
        string $id,
        array $errorDetails,
    ): ScheduledPostDto {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->status = ScheduledPostStatus::Failed->value;
        $model->error_details = $errorDetails;
        $model->error = $errorDetails;
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function incrementAttempt(string $workspaceId, string $id): ScheduledPostDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->attempt_count = (int) $model->attempt_count + 1;
        $model->last_attempt_at = now();
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function claimDueScheduledPosts(?string $workspaceId, int $limit): array
    {
        $limit = max(1, min($limit, 500));

        $query = ScheduledPost::query()
            ->where('status', ScheduledPostStatus::Scheduled->value)
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->whereNull('deleted_at')
            ->orderBy('scheduled_for');

        if ($workspaceId !== null && $workspaceId !== '') {
            $query->where('workspace_id', $workspaceId);
        }

        /** @var \Illuminate\Support\Collection<int, ScheduledPost> $models */
        $models = $query->limit($limit)->lockForUpdate()->get();

        $out = [];

        foreach ($models as $model) {
            $model->status = ScheduledPostStatus::Queued->value;
            $model->execution_id = (string) Str::uuid();
            $model->last_dispatched_at = now();
            $model->save();

            $out[] = $this->normalizer->normalize($model->fresh());
        }

        return $out;
    }

    public function listDueForDispatch(?string $workspaceId, int $limit = 100): array
    {
        $query = ScheduledPost::query()
            ->where('status', ScheduledPostStatus::Scheduled->value)
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->whereNull('deleted_at')
            ->orderBy('scheduled_for');

        if ($workspaceId !== null) {
            $query->where('workspace_id', $workspaceId);
        }

        return $query->limit($limit)->get()->map(fn (ScheduledPost $m) => $this->normalizer->normalize($m))->all();
    }

    public function markQueued(string $workspaceId, string $id): ScheduledPostDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->status = ScheduledPostStatus::Queued->value;
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    /**
     * @return list<ScheduledPostDto>
     */
    public function listStaleQueuedForRecovery(?string $workspaceId, \DateTimeInterface $threshold, int $limit): array
    {
        $limit = max(1, min($limit, 500));

        $q = ScheduledPost::query()
            ->where('status', ScheduledPostStatus::Queued->value)
            ->where('updated_at', '<', $threshold)
            ->whereNull('deleted_at')
            ->orderBy('updated_at');

        if ($workspaceId !== null && $workspaceId !== '') {
            $q->where('workspace_id', $workspaceId);
        }

        return $q->limit($limit)->get()->map(fn (ScheduledPost $m) => $this->normalizer->normalize($m))->all();
    }

    public function listRecentForWorkspace(string $workspaceId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));

        $models = $this->scopedQuery($workspaceId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $models->map(fn (ScheduledPost $m) => $this->normalizer->normalize($m))->all();
    }

    /**
     * @return Builder<ScheduledPost>
     */
    private function scopedQuery(string $workspaceId): Builder
    {
        return ScheduledPost::query()->where('workspace_id', $workspaceId);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Repositories;

use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use App\Domains\Integrations\Data\CreateLinkedInIntegrationDto;
use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use App\Domains\Integrations\Data\UpdateLinkedInIntegrationTokensDto;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Models\LinkedInIntegration;
use App\Domains\Integrations\Support\IntegrationNormalizer;
use Illuminate\Database\Eloquent\Builder;

final class LinkedInIntegrationRepository implements LinkedInIntegrationRepositoryContract
{
    public function __construct(
        private readonly IntegrationNormalizer $normalizer,
    ) {
    }

    public function findById(string $workspaceId, string $id): ?LinkedInIntegrationDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->first();

        return $model ? $this->normalizer->normalize($model) : null;
    }

    public function findByMemberId(string $workspaceId, string $linkedinMemberId): ?LinkedInIntegrationDto
    {
        $model = $this->scopedQuery($workspaceId)
            ->where('linkedin_member_id', $linkedinMemberId)
            ->first();

        return $model ? $this->normalizer->normalize($model) : null;
    }

    public function listByWorkspace(string $workspaceId, int $limit = 20): array
    {
        return $this->scopedQuery($workspaceId)
            ->orderByDesc('connected_at')
            ->limit($limit)
            ->get()
            ->map(fn (LinkedInIntegration $m) => $this->normalizer->normalize($m))
            ->all();
    }

    public function listExpiringBefore(\DateTimeInterface $before, int $limit = 50): array
    {
        return LinkedInIntegration::query()
            ->where('status', IntegrationStatus::Connected->value)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', $before)
            ->whereNotNull('refresh_token')
            ->orderBy('token_expires_at')
            ->limit($limit)
            ->get()
            ->map(fn (LinkedInIntegration $m) => $this->normalizer->normalize($m))
            ->all();
    }

    public function create(CreateLinkedInIntegrationDto $dto): LinkedInIntegrationDto
    {
        $model = LinkedInIntegration::query()->create([
            'workspace_id' => $dto->workspaceId,
            'provider' => $dto->provider->value,
            'linkedin_member_id' => $dto->linkedinMemberId,
            'access_token' => $dto->accessToken,
            'refresh_token' => $dto->refreshToken,
            'token_expires_at' => $dto->tokenExpiresAt,
            'scopes' => $dto->scopes,
            'metadata' => $dto->metadata,
            'status' => $dto->status->value,
            'connected_at' => now(),
            'last_error' => null,
            'refresh_attempts' => 0,
        ]);

        return $this->normalizer->normalize($model);
    }

    public function updateTokens(
        string $workspaceId,
        string $id,
        UpdateLinkedInIntegrationTokensDto $dto,
    ): LinkedInIntegrationDto {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();

        $model->access_token = $dto->accessToken;
        if ($dto->refreshToken !== null) {
            $model->refresh_token = $dto->refreshToken;
        }
        if ($dto->tokenExpiresAt !== null) {
            $model->token_expires_at = $dto->tokenExpiresAt;
        }
        if ($dto->scopes !== null) {
            $model->scopes = $dto->scopes;
        }
        if ($dto->metadata !== null) {
            $model->metadata = array_merge(
                is_array($model->metadata) ? $model->metadata : [],
                $dto->metadata,
            );
        }
        if ($dto->status !== null) {
            $model->status = $dto->status->value;
        }
        if ($dto->linkedinMemberId !== null) {
            $model->linkedin_member_id = $dto->linkedinMemberId;
        }
        if ($dto->resetRefreshAttempts) {
            $model->refresh_attempts = 0;
            $model->last_error = null;
        }

        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function updateStatus(
        string $workspaceId,
        string $id,
        IntegrationStatus $status,
        ?string $lastError = null,
    ): LinkedInIntegrationDto {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->status = $status->value;
        $model->last_error = $lastError;
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function markSynced(string $workspaceId, string $id): LinkedInIntegrationDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->last_synced_at = now();
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function incrementRefreshAttempts(string $workspaceId, string $id): LinkedInIntegrationDto
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->firstOrFail();
        $model->refresh_attempts = (int) $model->refresh_attempts + 1;
        $model->save();

        return $this->normalizer->normalize($model->fresh());
    }

    public function getDecryptedAccessToken(string $workspaceId, string $id): ?string
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->first();

        return $model?->access_token;
    }

    public function getDecryptedRefreshToken(string $workspaceId, string $id): ?string
    {
        $model = $this->scopedQuery($workspaceId)->where('id', $id)->first();

        return $model?->refresh_token;
    }

    /**
     * @return Builder<LinkedInIntegration>
     */
    private function scopedQuery(string $workspaceId): Builder
    {
        return LinkedInIntegration::query()->where('workspace_id', $workspaceId);
    }
}

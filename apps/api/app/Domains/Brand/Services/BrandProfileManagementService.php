<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\Brand\Contracts\BrandMemoryContextServiceContract;
use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Brand\Contracts\WritingSampleRepositoryContract;
use App\Domains\Brand\Contracts\WritingStyleExtractionServiceContract;
use App\Domains\Brand\Data\AudienceProfileDto;
use App\Domains\Brand\Data\BrandMemoryContext;
use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Brand\Data\CreateBrandProfileDto;
use App\Domains\Brand\Data\CreateWritingSampleDto;
use App\Domains\Brand\Data\StyleGuidelinesDto;
use App\Domains\Brand\Data\ToneProfileDto;
use App\Domains\Brand\Data\UpdateBrandProfileDto;
use App\Domains\Brand\Data\UpdateWritingSampleDto;
use App\Domains\Brand\Data\WritingSampleDto;
use App\Domains\Brand\Enums\WritingSampleSourceType;

/**
 * Brand profile + writing sample management for the settings UI.
 */
final class BrandProfileManagementService
{
    public function __construct(
        private readonly BrandProfileRepositoryContract $profiles,
        private readonly WritingSampleRepositoryContract $samples,
        private readonly BrandMemoryOrchestrationService $orchestration,
        private readonly BrandMemoryContextServiceContract $memoryContext,
        private readonly WritingStyleExtractionServiceContract $styleExtraction,
    ) {
    }

    /**
     * @return list<BrandProfileDto>
     */
    public function listProfiles(string $workspaceId): array
    {
        return $this->profiles->listByWorkspace($workspaceId);
    }

    public function getOrCreatePrimary(string $workspaceId): BrandProfileDto
    {
        $primary = $this->profiles->findPrimaryByWorkspace($workspaceId);
        if ($primary !== null) {
            return $primary;
        }

        return $this->profiles->create(new CreateBrandProfileDto(
            workspaceId: $workspaceId,
            name: 'Primary Brand',
            brandVoice: '',
            toneProfile: new ToneProfileDto(primary: 'professional'),
            targetAudience: new AudienceProfileDto(),
            isPrimary: true,
        ));
    }

    public function getProfile(string $workspaceId, string $profileId): ?BrandProfileDto
    {
        return $this->profiles->findById($workspaceId, $profileId);
    }

    public function updateProfile(
        string $workspaceId,
        string $profileId,
        UpdateBrandProfileDto $dto,
    ): BrandProfileDto {
        return $this->profiles->update($workspaceId, $profileId, $dto);
    }

    public function setPrimary(string $workspaceId, string $profileId): BrandProfileDto
    {
        return $this->profiles->setPrimary($workspaceId, $profileId);
    }

    /**
     * @return list<WritingSampleDto>
     */
    public function listWritingSamples(string $workspaceId, string $profileId): array
    {
        return $this->samples->listByProfile($workspaceId, $profileId);
    }

    public function createWritingSample(CreateWritingSampleDto $dto): WritingSampleDto
    {
        return $this->orchestration->ingestWritingSample($dto);
    }

    public function updateWritingSample(
        string $workspaceId,
        string $sampleId,
        UpdateWritingSampleDto $dto,
    ): WritingSampleDto {
        $normalized = [];
        if ($dto->reextractStyle === true && $dto->content !== null) {
            $normalized = $this->styleExtraction->extract($dto->content)->toArray();
        }

        $sample = $this->samples->update($workspaceId, $sampleId, $dto, $normalized);

        if ($sample->brandProfileId !== null) {
            $this->profiles->incrementMemoryVersion($workspaceId, $sample->brandProfileId);
        }

        return $sample;
    }

    public function deleteWritingSample(string $workspaceId, string $sampleId): void
    {
        $sample = $this->samples->findById($workspaceId, $sampleId);
        $this->samples->delete($workspaceId, $sampleId);

        if ($sample?->brandProfileId !== null) {
            $this->profiles->incrementMemoryVersion($workspaceId, $sample->brandProfileId);
        }
    }

    public function memoryPreview(
        string $workspaceId,
        string $hookQueryText,
        ?string $targetAudience = null,
        ?string $contentPillar = null,
    ): BrandMemoryContext {
        return $this->memoryContext->forHookAgent(
            $workspaceId,
            $hookQueryText,
            $targetAudience,
            $contentPillar,
        );
    }

    public static function parseSourceType(string $value): WritingSampleSourceType
    {
        return WritingSampleSourceType::fromString($value);
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Brand\Contracts\WritingSampleRepositoryContract;
use App\Domains\Brand\Contracts\WritingStyleExtractionServiceContract;
use App\Domains\Brand\Data\CreateWritingSampleDto;
use App\Domains\Brand\Data\WritingSampleDto;

/**
 * Write-side orchestration: ingest samples, extract style, bump memory version.
 */
final class BrandMemoryOrchestrationService
{
    public function __construct(
        private readonly WritingSampleRepositoryContract $samples,
        private readonly BrandProfileRepositoryContract $profiles,
        private readonly WritingStyleExtractionServiceContract $styleExtraction,
    ) {
    }

    public function ingestWritingSample(CreateWritingSampleDto $dto): WritingSampleDto
    {
        $normalized = $dto->extractStyle
            ? $this->styleExtraction->extract($dto->content)->toArray()
            : [];

        $sample = $this->samples->create($dto, $normalized);

        if ($dto->brandProfileId !== null) {
            $this->profiles->incrementMemoryVersion($dto->workspaceId, $dto->brandProfileId);
        } elseif ($profile = $this->profiles->findPrimaryByWorkspace($dto->workspaceId)) {
            $this->profiles->incrementMemoryVersion($dto->workspaceId, $profile->id);
        }

        return $sample;
    }
}

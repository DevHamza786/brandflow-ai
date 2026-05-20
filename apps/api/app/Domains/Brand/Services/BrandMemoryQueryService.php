<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\Brand\Contracts\BrandMemoryEnrichmentServiceContract;
use App\Domains\Brand\Contracts\BrandMemoryQueryServiceContract;
use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Brand\Contracts\WritingSampleRepositoryContract;
use App\Domains\Brand\Data\BrandMemoryEnrichmentDto;
use App\Domains\Brand\Data\BrandProfileDto;

/**
 * Read-side query layer for brand memory (RAG-ready, workspace-scoped).
 */
final class BrandMemoryQueryService implements BrandMemoryQueryServiceContract
{
    public function __construct(
        private readonly BrandProfileRepositoryContract $profiles,
        private readonly WritingSampleRepositoryContract $samples,
        private readonly BrandMemoryEnrichmentServiceContract $enrichment,
    ) {
    }

    public function findPrimaryProfile(string $workspaceId): ?BrandProfileDto
    {
        return $this->profiles->findPrimaryByWorkspace($workspaceId);
    }

    public function enrichForWorkspace(
        string $workspaceId,
        ?string $query = null,
        int $sampleLimit = 5,
    ): BrandMemoryEnrichmentDto {
        $profile = $this->profiles->findPrimaryByWorkspace($workspaceId);

        if ($profile === null) {
            return new BrandMemoryEnrichmentDto(
                workspaceId: $workspaceId,
                memoryVersion: 1,
                profile: null,
                promptVariables: [],
                memoryChunks: [],
                analyticsPayload: ['workspace_id' => $workspaceId, 'profile_found' => false],
            );
        }

        $writingSamples = $this->samples->listByWorkspace($workspaceId, $sampleLimit);

        return $this->enrichment->enrich($profile, $writingSamples, $query);
    }
}

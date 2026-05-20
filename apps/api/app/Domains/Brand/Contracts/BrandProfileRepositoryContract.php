<?php

declare(strict_types=1);

namespace App\Domains\Brand\Contracts;

use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Brand\Data\CreateBrandProfileDto;
use App\Domains\Brand\Data\UpdateBrandProfileDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface BrandProfileRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findById(string $workspaceId, string $id): ?BrandProfileDto;

    public function findPrimaryByWorkspace(string $workspaceId): ?BrandProfileDto;

    /**
     * @return list<BrandProfileDto>
     */
    public function listByWorkspace(string $workspaceId, int $limit = 50): array;

    public function create(CreateBrandProfileDto $dto): BrandProfileDto;

    public function update(string $workspaceId, string $profileId, UpdateBrandProfileDto $dto): BrandProfileDto;

    public function incrementMemoryVersion(string $workspaceId, string $profileId): BrandProfileDto;

    public function setPrimary(string $workspaceId, string $profileId): BrandProfileDto;
}

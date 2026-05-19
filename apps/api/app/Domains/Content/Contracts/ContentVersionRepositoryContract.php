<?php

declare(strict_types=1);

namespace App\Domains\Content\Contracts;

use App\Domains\Content\Models\ContentVersion;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface ContentVersionRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findForWorkspace(string $workspaceId, string $contentVersionId): ?ContentVersion;

    public function findForWorkspaceOrFail(string $workspaceId, string $contentVersionId): ContentVersion;
}

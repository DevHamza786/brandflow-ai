<?php

declare(strict_types=1);

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Contracts\ContentVersionRepositoryContract;
use App\Domains\Content\Models\ContentVersion;

final class ContentVersionRepository implements ContentVersionRepositoryContract
{
    public function findForWorkspace(string $workspaceId, string $contentVersionId): ?ContentVersion
    {
        return ContentVersion::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($contentVersionId)
            ->first();
    }

    public function findForWorkspaceOrFail(string $workspaceId, string $contentVersionId): ContentVersion
    {
        return ContentVersion::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($contentVersionId)
            ->firstOrFail();
    }
}

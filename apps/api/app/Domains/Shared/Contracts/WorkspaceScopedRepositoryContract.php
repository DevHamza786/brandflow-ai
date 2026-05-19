<?php

declare(strict_types=1);

namespace App\Domains\Shared\Contracts;

/**
 * Repository contract for aggregates scoped by workspace (tenant).
 */
interface WorkspaceScopedRepositoryContract extends RepositoryContract
{
}

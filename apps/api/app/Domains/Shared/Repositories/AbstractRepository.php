<?php

declare(strict_types=1);

namespace App\Domains\Shared\Repositories;

use App\Domains\Shared\Contracts\RepositoryContract;

/**
 * Optional base for Eloquent-backed repositories (implement in a later iteration).
 */
abstract class AbstractRepository implements RepositoryContract
{
}

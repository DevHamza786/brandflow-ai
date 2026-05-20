<?php

declare(strict_types=1);

namespace App\Domains\AI\Actions;

use App\Domains\AI\Contracts\GeneratedOutputPersistenceContract;
use App\Domains\AI\Data\CreateGeneratedOutputDto;
use App\Domains\AI\Data\GeneratedOutputDto;

/**
 * Thin orchestrator for HTTP / workflow entry points.
 */
final class PersistGeneratedOutputAction
{
    public function __construct(
        private readonly GeneratedOutputPersistenceContract $persistence,
    ) {
    }

    public function begin(CreateGeneratedOutputDto $dto): GeneratedOutputDto
    {
        return $this->persistence->begin($dto);
    }

    public function record(CreateGeneratedOutputDto $dto): GeneratedOutputDto
    {
        return $this->persistence->record($dto);
    }
}

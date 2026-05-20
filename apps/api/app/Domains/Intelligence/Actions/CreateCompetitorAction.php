<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Actions;

use App\Domains\Intelligence\Contracts\CompetitorRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorDto;
use App\Domains\Intelligence\Data\CreateCompetitorDto;

final class CreateCompetitorAction
{
    public function __construct(
        private readonly CompetitorRepositoryContract $competitors,
    ) {
    }

    public function execute(CreateCompetitorDto $dto): CompetitorDto
    {
        return $this->competitors->create($dto);
    }
}

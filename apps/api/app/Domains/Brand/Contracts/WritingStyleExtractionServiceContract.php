<?php

declare(strict_types=1);

namespace App\Domains\Brand\Contracts;

use App\Domains\Brand\Data\NormalizedStyleDataDto;

interface WritingStyleExtractionServiceContract
{
    public function extract(string $content): NormalizedStyleDataDto;
}

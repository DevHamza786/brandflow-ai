<?php

declare(strict_types=1);

namespace App\Domains\Brand\Contracts;

use App\Domains\Brand\Data\BrandMemoryEnrichmentDto;
use App\Domains\Brand\Data\BrandProfileDto;

interface BrandMemoryEnrichmentServiceContract
{
    /**
     * @param  list<\App\Domains\Brand\Data\WritingSampleDto>  $samples
     */
    public function enrich(
        BrandProfileDto $profile,
        array $samples = [],
        ?string $query = null,
    ): BrandMemoryEnrichmentDto;
}

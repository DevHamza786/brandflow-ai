<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Contracts;

interface ExperimentationMlCompatibilityLayerContract
{
    /**
     * @param  array<string, mixed>  $mlState
     * @param  array<string, mixed>  $comparison
     * @return array<string, mixed>
     */
    public function afterComparison(array $mlState, array $comparison): array;
}

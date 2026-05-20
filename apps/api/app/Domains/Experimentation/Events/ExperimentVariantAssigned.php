<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class ExperimentVariantAssigned
{
    use Dispatchable;

    public function __construct(
        public readonly string $workspaceId,
        public readonly string $experimentId,
        public readonly string $variantKey,
        public readonly string $subjectKey,
    ) {
    }
}

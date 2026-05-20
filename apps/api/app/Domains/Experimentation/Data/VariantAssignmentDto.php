<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Data;

use App\Domains\Shared\Data\DataTransferObject;

final class VariantAssignmentDto extends DataTransferObject
{
    public function __construct(
        public readonly string $experimentId,
        public readonly ExperimentVariantDto $variant,
        public readonly string $subjectKey,
        public readonly bool $wasExisting,
        public readonly ?string $assignmentResultId,
    ) {
    }
}

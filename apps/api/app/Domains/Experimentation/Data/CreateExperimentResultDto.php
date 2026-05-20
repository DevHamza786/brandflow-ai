<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Data;

use App\Domains\Experimentation\Enums\ExperimentResultType;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  array<string, mixed>  $metrics
 * @param  array<string, mixed>  $statisticalSummary
 */
final class CreateExperimentResultDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $experimentId,
        public readonly ExperimentResultType $resultType,
        public readonly ?string $experimentVariantId = null,
        public readonly ?string $entityType = null,
        public readonly ?string $entityId = null,
        public readonly ?string $subjectKey = null,
        public readonly array $metrics = [],
        public readonly array $statisticalSummary = [],
        public readonly ?string $idempotencyKey = null,
        public readonly ?string $traceId = null,
    ) {
    }
}

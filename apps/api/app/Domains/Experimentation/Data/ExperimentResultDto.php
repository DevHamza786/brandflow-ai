<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Data;

use App\Domains\Experimentation\Enums\ExperimentResultType;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $metrics
 * @param  array<string, mixed>  $statisticalSummary
 */
final class ExperimentResultDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $experimentId,
        public readonly ?string $experimentVariantId,
        public readonly ExperimentResultType $resultType,
        public readonly ?string $entityType,
        public readonly ?string $entityId,
        public readonly ?string $subjectKey,
        public readonly array $metrics,
        public readonly array $statisticalSummary,
        public readonly ?string $traceId,
        public readonly CarbonInterface $createdAt,
    ) {
    }
}

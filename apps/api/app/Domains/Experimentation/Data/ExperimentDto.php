<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Data;

use App\Domains\Experimentation\Enums\ExperimentStatus;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $config
 * @param  array<string, mixed>  $mlState
 * @param  array<string, mixed>  $metadata
 */
final class ExperimentDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $slug,
        public readonly string $name,
        public readonly ExperimentType $experimentType,
        public readonly ExperimentStatus $status,
        public readonly ?string $hypothesis,
        public readonly array $config,
        public readonly array $mlState,
        public readonly array $metadata,
        public readonly ?string $optimizationLoopId,
        public readonly ?string $workflowBlueprintId,
        public readonly ?string $agentCoordinationId,
        public readonly ?CarbonInterface $startedAt,
        public readonly ?CarbonInterface $endedAt,
    ) {
    }
}

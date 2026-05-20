<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Data;

use App\Domains\Autonomous\Enums\AutonomousWorkflowMode;
use App\Domains\Autonomous\Enums\AutonomousWorkflowStatus;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  array<string, mixed>|null  $configPatch
 */
final class UpdateAutonomousWorkflowDto extends DataTransferObject
{
    public function __construct(
        public readonly ?AutonomousWorkflowStatus $status = null,
        public readonly ?AutonomousWorkflowMode $mode = null,
        public readonly ?bool $manualOverrideEnabled = null,
        public readonly ?bool $autonomousExecutionEnabled = null,
        public readonly ?array $configPatch = null,
        public readonly ?float $minConfidence = null,
    ) {
    }
}

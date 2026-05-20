<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * @param  list<string>  $errors
 * @param  list<string>  $warnings
 */
final class ValidateBlueprintResultDto extends DataTransferObject
{
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors,
        public readonly array $warnings,
    ) {
    }
}

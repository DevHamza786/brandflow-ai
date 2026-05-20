<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\AI\Enums\GeneratedOutputStatus;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * Command DTO for creating or upserting a generated output row.
 */
final class CreateGeneratedOutputDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly GeneratedOutputType $type,
        public readonly GeneratedOutputInputDto $input,
        public readonly ?string $workflowRunId = null,
        public readonly ?string $agentRunId = null,
        public readonly ?string $contentVersionId = null,
        public readonly ?string $provider = null,
        public readonly ?string $model = null,
        public readonly ?string $promptVersion = null,
        public readonly ?GeneratedOutputPayloadDto $output = null,
        public readonly ?GeneratedOutputScoresDto $scores = null,
        public readonly ?GeneratedOutputMetadataDto $metadata = null,
        public readonly GeneratedOutputStatus $status = GeneratedOutputStatus::Pending,
    ) {
    }
}

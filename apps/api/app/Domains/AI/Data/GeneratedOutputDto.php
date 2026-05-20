<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\AI\Enums\GeneratedOutputStatus;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\AI\Models\GeneratedOutput;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * Read model returned from repository / service boundaries.
 */
final class GeneratedOutputDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly GeneratedOutputType $type,
        public readonly GeneratedOutputStatus $status,
        public readonly GeneratedOutputInputDto $input,
        public readonly ?GeneratedOutputPayloadDto $output,
        public readonly GeneratedOutputScoresDto $scores,
        public readonly GeneratedOutputMetadataDto $metadata,
        public readonly ?string $workflowRunId = null,
        public readonly ?string $agentRunId = null,
        public readonly ?string $contentVersionId = null,
        public readonly ?string $provider = null,
        public readonly ?string $model = null,
        public readonly ?string $promptVersion = null,
        public readonly ?CarbonInterface $createdAt = null,
        public readonly ?CarbonInterface $updatedAt = null,
    ) {
    }

    public static function fromModel(GeneratedOutput $model): self
    {
        return new self(
            id: (string) $model->id,
            workspaceId: (string) $model->workspace_id,
            type: GeneratedOutputType::fromString((string) $model->type),
            status: GeneratedOutputStatus::fromString((string) $model->status),
            input: GeneratedOutputInputDto::fromArray($model->input ?? []),
            output: $model->output !== null
                ? GeneratedOutputPayloadDto::fromArray($model->output)
                : null,
            scores: GeneratedOutputScoresDto::fromArray($model->scores ?? []),
            metadata: GeneratedOutputMetadataDto::fromArray($model->metadata ?? []),
            workflowRunId: $model->workflow_run_id,
            agentRunId: $model->agent_run_id,
            contentVersionId: $model->content_version_id,
            provider: $model->provider,
            model: $model->model,
            promptVersion: $model->prompt_version,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use App\Domains\Experimentation\Contracts\ExperimentResultRepositoryContract;
use App\Domains\Experimentation\Contracts\ExperimentVariantRepositoryContract;
use App\Domains\Experimentation\Data\CreateExperimentResultDto;
use App\Domains\Experimentation\Data\ExperimentDto;
use App\Domains\Experimentation\Data\ExperimentVariantDto;
use App\Domains\Experimentation\Data\VariantAssignmentDto;
use App\Domains\Experimentation\Enums\ExperimentResultType;
use App\Domains\Experimentation\Events\ExperimentVariantAssigned;

/**
 * Sticky, weighted variant assignment with idempotent persistence.
 */
final class VariantAssignmentEngine
{
    public function __construct(
        private readonly ExperimentVariantRepositoryContract $variants,
        private readonly ExperimentResultRepositoryContract $results,
    ) {
    }

    public function assign(
        string $workspaceId,
        ExperimentDto $experiment,
        string $subjectKey,
        ?string $traceId = null,
    ): VariantAssignmentDto {
        $variantList = $this->variants->ensureTemplateVariants(
            $workspaceId,
            $experiment->id,
            $experiment->experimentType,
        );

        $existing = $this->results->findAssignment($workspaceId, $experiment->id, $subjectKey);
        if ($existing !== null && $existing->experimentVariantId !== null) {
            $variant = $this->findVariant($variantList, $existing->experimentVariantId);

            return new VariantAssignmentDto(
                experimentId: $experiment->id,
                variant: $variant,
                subjectKey: $subjectKey,
                wasExisting: true,
                assignmentResultId: $existing->id,
            );
        }

        $chosen = $this->pickVariant($experiment->id, $subjectKey, $variantList);

        $result = $this->results->create(new CreateExperimentResultDto(
            workspaceId: $workspaceId,
            experimentId: $experiment->id,
            resultType: ExperimentResultType::Assignment,
            experimentVariantId: $chosen->id,
            subjectKey: $subjectKey,
            metrics: ['variant_key' => $chosen->variantKey],
            idempotencyKey: "assign:{$experiment->id}:{$subjectKey}",
            traceId: $traceId,
        ));

        $this->variants->incrementAssignmentCount($workspaceId, $chosen->id);

        event(new ExperimentVariantAssigned($workspaceId, $experiment->id, $chosen->variantKey, $subjectKey));

        return new VariantAssignmentDto(
            experimentId: $experiment->id,
            variant: $chosen,
            subjectKey: $subjectKey,
            wasExisting: false,
            assignmentResultId: $result->id,
        );
    }

    /**
     * @param  list<ExperimentVariantDto>  $variants
     */
    private function pickVariant(string $experimentId, string $subjectKey, array $variants): ExperimentVariantDto
    {
        if ($variants === []) {
            throw new \RuntimeException('Experiment has no variants.');
        }

        $hash = crc32($experimentId.':'.$subjectKey);
        $bucket = ($hash % 10000) / 10000;

        $cursor = 0.0;
        foreach ($variants as $variant) {
            $cursor += $variant->trafficWeight;
            if ($bucket <= $cursor) {
                return $variant;
            }
        }

        return $variants[array_key_last($variants)];
    }

    /**
     * @param  list<ExperimentVariantDto>  $variants
     */
    private function findVariant(array $variants, string $variantId): ExperimentVariantDto
    {
        foreach ($variants as $variant) {
            if ($variant->id === $variantId) {
                return $variant;
            }
        }

        throw new \RuntimeException("Variant [{$variantId}] not found.");
    }
}

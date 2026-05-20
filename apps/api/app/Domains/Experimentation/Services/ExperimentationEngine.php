<?php

declare(strict_types=1);

namespace App\Domains\Experimentation\Services;

use App\Domains\Experimentation\Contracts\ExperimentRepositoryContract;
use App\Domains\Experimentation\Contracts\ExperimentResultRepositoryContract;
use App\Domains\Experimentation\Contracts\ExperimentVariantRepositoryContract;
use App\Domains\Experimentation\Contracts\ExperimentationMlCompatibilityLayerContract;
use App\Domains\Experimentation\Data\CreateExperimentResultDto;
use App\Domains\Experimentation\Data\StatisticalComparisonDto;
use App\Domains\Experimentation\Data\VariantAssignmentDto;
use App\Domains\Experimentation\Enums\ExperimentResultType;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Experimentation\Events\ExperimentComparisonCompleted;
use App\Domains\Experimentation\Events\ExperimentObservationRecorded;
use App\Domains\Experimentation\Events\ExperimentStarted;
use App\Domains\Experimentation\Models\Experiment;
use Illuminate\Support\Str;

/**
 * Orchestrates assign → observe → compare with isolation and idempotency.
 */
final class ExperimentationEngine
{
    public function __construct(
        private readonly ExperimentRepositoryContract $experiments,
        private readonly ExperimentVariantRepositoryContract $variants,
        private readonly ExperimentResultRepositoryContract $results,
        private readonly VariantAssignmentEngine $assignment,
        private readonly ExperimentScoringEngine $scoring,
        private readonly StatisticalComparisonEngine $comparison,
        private readonly ExperimentAnalyticsIntegration $analytics,
        private readonly ExperimentOptimizationIntegration $optimization,
        private readonly ExperimentRecommendationIntegration $recommendations,
        private readonly ExperimentationMlCompatibilityLayerContract $mlLayer,
        private readonly ExperimentExecutionLogger $logger,
    ) {
    }

    public function ensureExperiment(string $workspaceId, ExperimentType $type): \App\Domains\Experimentation\Data\ExperimentDto
    {
        $experiment = $this->experiments->findOrCreateByType($workspaceId, $type);
        $experiment = $this->optimization->linkLoop($workspaceId, $experiment);
        $this->variants->ensureTemplateVariants($workspaceId, $experiment->id, $type);
        $this->experiments->markRunning($workspaceId, $experiment->id);

        event(new ExperimentStarted($workspaceId, $experiment->id, $type->value));

        return $experiment;
    }

    public function assignVariant(
        string $workspaceId,
        ExperimentType $type,
        string $subjectKey,
    ): VariantAssignmentDto {
        $experiment = $this->ensureExperiment($workspaceId, $type);
        $traceId = 'exp_'.Str::uuid()->toString();

        $assignment = $this->assignment->assign($workspaceId, $experiment, $subjectKey, $traceId);

        $this->logger->info('variant_assigned', [
            'experiment_id' => $experiment->id,
            'variant_key' => $assignment->variant->variantKey,
            'subject_key' => $subjectKey,
            'analytics_refs' => $this->analytics->contextRefs($workspaceId),
        ]);

        return $assignment;
    }

    /**
     * @param  array{impressions?: int, engagements?: int, normalized_score?: float}  $metrics
     */
    public function recordObservation(
        string $workspaceId,
        string $experimentId,
        string $variantId,
        string $subjectKey,
        array $metrics,
        ?string $entityType = null,
        ?string $entityId = null,
    ): void {
        $this->results->create(new CreateExperimentResultDto(
            workspaceId: $workspaceId,
            experimentId: $experimentId,
            resultType: ExperimentResultType::Observation,
            experimentVariantId: $variantId,
            entityType: $entityType,
            entityId: $entityId,
            subjectKey: $subjectKey,
            metrics: $metrics,
            idempotencyKey: "observe:{$experimentId}:{$subjectKey}:".md5(json_encode($metrics)),
        ));

        event(new ExperimentObservationRecorded($workspaceId, $experimentId, $variantId));
    }

    public function compareExperiment(string $workspaceId, string $experimentId): StatisticalComparisonDto
    {
        $variantList = $this->variants->listByExperiment($workspaceId, $experimentId);
        $control = null;
        $challenger = null;

        foreach ($variantList as $variant) {
            if ($variant->isControl) {
                $control = $variant;
            } elseif ($challenger === null) {
                $challenger = $variant;
            }
        }

        if ($control === null || $challenger === null) {
            throw new \InvalidArgumentException('Experiment requires control and challenger variants.');
        }

        $controlObs = $this->loadObservations($workspaceId, $control);
        $variantObs = $this->loadObservations($workspaceId, $challenger);

        if ($controlObs === [] || $variantObs === []) {
            $controlObs = $this->analytics->syntheticObservationsForVariant($workspaceId, $control->variantKey);
            $variantObs = $this->analytics->syntheticObservationsForVariant($workspaceId, $challenger->variantKey);
        }

        $comparison = $this->comparison->compare(
            $experimentId,
            $control,
            $challenger,
            $controlObs,
            $variantObs,
        );

        $this->results->create(new CreateExperimentResultDto(
            workspaceId: $workspaceId,
            experimentId: $experimentId,
            resultType: ExperimentResultType::Comparison,
            statisticalSummary: $this->recommendations->comparisonPayload($comparison),
            idempotencyKey: "compare:{$experimentId}:".now()->format('Y-m-d-H'),
        ));

        $model = Experiment::query()->find($experimentId);
        if ($model !== null) {
            $model->update([
                'ml_state' => $this->mlLayer->afterComparison($model->ml_state ?? [], $comparison->toArray()),
            ]);
        }

        event(new ExperimentComparisonCompleted($workspaceId, $experimentId, $comparison));

        $this->logger->info('comparison_completed', [
            'experiment_id' => $experimentId,
            'narrative' => $comparison->narrative,
            'is_significant' => $comparison->isSignificant,
        ]);

        return $comparison;
    }

    /**
     * @return list<array{impressions: int, engagements: int, normalized_score: float}>
     */
    private function loadObservations(string $workspaceId, \App\Domains\Experimentation\Data\ExperimentVariantDto $variant): array
    {
        $rows = $this->results->listObservationsByVariant($workspaceId, $variant->id);
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'impressions' => (int) ($row->metrics['impressions'] ?? 0),
                'engagements' => (int) ($row->metrics['engagements'] ?? 0),
                'normalized_score' => (float) ($row->metrics['normalized_score'] ?? 0),
            ];
        }

        return $out;
    }
}

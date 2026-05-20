<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AnalyticsDashboardController;
use App\Http\Controllers\Api\V1\CompetitorsController;
use App\Http\Controllers\Api\V1\AutonomousController;
use App\Http\Controllers\Api\V1\CoordinationController;
use App\Http\Controllers\Api\V1\OptimizationController;
use App\Http\Controllers\Api\V1\WorkflowBuilderController;
use App\Http\Controllers\Api\V1\ExperimentationController;
use App\Http\Controllers\Api\V1\RecommendationsController;
use App\Http\Controllers\Api\V1\BrandProfileController;
use App\Http\Controllers\Api\V1\LinkedInOAuthController;
use App\Http\Controllers\Api\V1\HookGenerationController;
use App\Http\Controllers\Api\V1\PublishLinkedInController;
use App\Http\Controllers\Api\V1\ScheduledPostsController;
use App\Http\Controllers\Api\V1\ResultsController;
use App\Http\Controllers\Api\V1\WritingSampleController;
use App\Http\Middleware\ResolveWorkspace;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware([ResolveWorkspace::class])
    ->withoutMiddleware([SubstituteBindings::class])
    ->group(function (): void {
        // Use {versionId} — not {contentVersionId} (Laravel implicit binding treats that as ContentVersion).
        Route::post(
            'content-versions/{versionId}/hooks/generate',
            [HookGenerationController::class, 'store'],
        )->name('api.v1.hooks.generate');

        Route::get(
            'agents/runs/{agentRunId}',
            [ResultsController::class, 'show'],
        )->name('api.v1.agents.runs.show');

        Route::get(
            'agents/runs/{agentRunId}/results',
            [ResultsController::class, 'results'],
        )->name('api.v1.agents.runs.results');

        Route::get('brand-profiles', [BrandProfileController::class, 'index'])
            ->name('api.v1.brand-profiles.index');
        Route::get('brand-profiles/primary', [BrandProfileController::class, 'primary'])
            ->name('api.v1.brand-profiles.primary');
        Route::get('brand-profiles/{profileId}', [BrandProfileController::class, 'show'])
            ->name('api.v1.brand-profiles.show');
        Route::patch('brand-profiles/{profileId}', [BrandProfileController::class, 'update'])
            ->name('api.v1.brand-profiles.update');
        Route::post('brand-profiles/{profileId}/primary', [BrandProfileController::class, 'setPrimary'])
            ->name('api.v1.brand-profiles.set-primary');
        Route::get('brand-profiles/{profileId}/memory-preview', [BrandProfileController::class, 'memoryPreview'])
            ->name('api.v1.brand-profiles.memory-preview');

        Route::get('brand-profiles/{profileId}/writing-samples', [WritingSampleController::class, 'index'])
            ->name('api.v1.writing-samples.index');
        Route::post('brand-profiles/{profileId}/writing-samples', [WritingSampleController::class, 'store'])
            ->name('api.v1.writing-samples.store');
        Route::patch('writing-samples/{sampleId}', [WritingSampleController::class, 'update'])
            ->name('api.v1.writing-samples.update');
        Route::delete('writing-samples/{sampleId}', [WritingSampleController::class, 'destroy'])
            ->name('api.v1.writing-samples.destroy');

        Route::get('integrations/linkedin/connect', [LinkedInOAuthController::class, 'connect'])
            ->name('api.v1.integrations.linkedin.connect');
        Route::get('integrations/linkedin', [LinkedInOAuthController::class, 'index'])
            ->name('api.v1.integrations.linkedin.index');
        Route::get('integrations/linkedin/{integrationId}', [LinkedInOAuthController::class, 'show'])
            ->name('api.v1.integrations.linkedin.show');
        Route::delete('integrations/linkedin/{integrationId}', [LinkedInOAuthController::class, 'destroy'])
            ->name('api.v1.integrations.linkedin.destroy');

        Route::post('publish/linkedin', [PublishLinkedInController::class, 'store'])
            ->name('api.v1.publish.linkedin');

        Route::get('scheduled-posts', [ScheduledPostsController::class, 'index'])
            ->name('api.v1.scheduled-posts.index');
        Route::get('scheduled-posts/{scheduledPostId}', [ScheduledPostsController::class, 'show'])
            ->name('api.v1.scheduled-posts.show');

        Route::get('analytics/dashboard', [AnalyticsDashboardController::class, 'show'])
            ->name('api.v1.analytics.dashboard');

        Route::get('recommendations', [RecommendationsController::class, 'index'])
            ->name('api.v1.recommendations.index');
        Route::post('recommendations/generate', [RecommendationsController::class, 'generate'])
            ->name('api.v1.recommendations.generate');
        Route::get('recommendations/{recommendationId}', [RecommendationsController::class, 'show'])
            ->name('api.v1.recommendations.show');

        Route::get('optimization/loops', [OptimizationController::class, 'indexLoops'])
            ->name('api.v1.optimization.loops.index');
        Route::get('optimization/loops/{loopId}', [OptimizationController::class, 'showLoop'])
            ->name('api.v1.optimization.loops.show');
        Route::get('optimization/snapshots', [OptimizationController::class, 'indexSnapshots'])
            ->name('api.v1.optimization.snapshots.index');
        Route::get('optimization/snapshots/{snapshotId}', [OptimizationController::class, 'showSnapshot'])
            ->name('api.v1.optimization.snapshots.show');
        Route::post('optimization/cycles/run', [OptimizationController::class, 'runCycle'])
            ->name('api.v1.optimization.cycles.run');

        Route::get('autonomous/workflows', [AutonomousController::class, 'indexWorkflows'])
            ->name('api.v1.autonomous.workflows.index');
        Route::get('autonomous/workflows/{workflowId}', [AutonomousController::class, 'showWorkflow'])
            ->name('api.v1.autonomous.workflows.show');
        Route::patch('autonomous/workflows/{workflowId}', [AutonomousController::class, 'updateWorkflow'])
            ->name('api.v1.autonomous.workflows.update');
        Route::get('autonomous/snapshots', [AutonomousController::class, 'indexSnapshots'])
            ->name('api.v1.autonomous.snapshots.index');
        Route::get('autonomous/snapshots/{snapshotId}', [AutonomousController::class, 'showSnapshot'])
            ->name('api.v1.autonomous.snapshots.show');
        Route::post('autonomous/executions/run', [AutonomousController::class, 'runExecution'])
            ->name('api.v1.autonomous.executions.run');

        Route::get('coordination/sessions', [CoordinationController::class, 'index'])
            ->name('api.v1.coordination.sessions.index');
        Route::get('coordination/sessions/{coordinationId}', [CoordinationController::class, 'show'])
            ->name('api.v1.coordination.sessions.show');
        Route::get('coordination/snapshots', [CoordinationController::class, 'indexSnapshots'])
            ->name('api.v1.coordination.snapshots.index');
        Route::post('coordination/cycles/run', [CoordinationController::class, 'runCycle'])
            ->name('api.v1.coordination.cycles.run');
        Route::get('coordination/routing/preview', [CoordinationController::class, 'previewRouting'])
            ->name('api.v1.coordination.routing.preview');

        Route::get('workflow-builder/blueprints', [WorkflowBuilderController::class, 'indexBlueprints'])
            ->name('api.v1.workflow-builder.blueprints.index');
        Route::get('workflow-builder/blueprints/{blueprintId}', [WorkflowBuilderController::class, 'showBlueprint'])
            ->name('api.v1.workflow-builder.blueprints.show');
        Route::get('workflow-builder/blueprints/{blueprintId}/validate', [WorkflowBuilderController::class, 'validateBlueprint'])
            ->name('api.v1.workflow-builder.blueprints.validate');
        Route::post('workflow-builder/blueprints/{blueprintId}/execute', [WorkflowBuilderController::class, 'executeBlueprint'])
            ->name('api.v1.workflow-builder.blueprints.execute');
        Route::post('workflow-builder/execute', [WorkflowBuilderController::class, 'executeBlueprint'])
            ->name('api.v1.workflow-builder.execute.default');

        Route::get('experiments', [ExperimentationController::class, 'index'])
            ->name('api.v1.experiments.index');
        Route::get('experiments/{experimentId}', [ExperimentationController::class, 'show'])
            ->name('api.v1.experiments.show');
        Route::post('experiments/assign', [ExperimentationController::class, 'assign'])
            ->name('api.v1.experiments.assign');
        Route::post('experiments/{experimentId}/compare', [ExperimentationController::class, 'compare'])
            ->name('api.v1.experiments.compare');
        Route::post('experiments/demo-cycle', [ExperimentationController::class, 'runDemoCycle'])
            ->name('api.v1.experiments.demo-cycle');

        Route::get('competitors', [CompetitorsController::class, 'index'])
            ->name('api.v1.competitors.index');
        Route::post('competitors', [CompetitorsController::class, 'store'])
            ->name('api.v1.competitors.store');
        Route::get('competitors/{competitorId}', [CompetitorsController::class, 'show'])
            ->name('api.v1.competitors.show');
        Route::post('competitors/{competitorId}/snapshots', [CompetitorsController::class, 'ingestSnapshot'])
            ->name('api.v1.competitors.snapshots.ingest');
    });

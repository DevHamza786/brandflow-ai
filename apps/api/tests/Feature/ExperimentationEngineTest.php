<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Experimentation\Actions\AssignExperimentVariantAction;
use App\Domains\Experimentation\Actions\CompareExperimentAction;
use App\Domains\Experimentation\Enums\ExperimentResultType;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Experimentation\Models\ExperimentResult;
use App\Domains\Experimentation\Services\ExperimentationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ExperimentationEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_hook_ab_variant_assignment_is_sticky(): void
    {
        $workspaceId = $this->createWorkspace();

        $first = app(AssignExperimentVariantAction::class)->execute(
            $workspaceId,
            ExperimentType::HookAb,
            'subject:hook-1',
        );
        $second = app(AssignExperimentVariantAction::class)->execute(
            $workspaceId,
            ExperimentType::HookAb,
            'subject:hook-1',
        );

        $this->assertTrue($second->wasExisting);
        $this->assertSame($first->variant->id, $second->variant->id);

        $assignment = ExperimentResult::query()
            ->where('workspace_id', $workspaceId)
            ->where('result_type', ExperimentResultType::Assignment->value)
            ->where('subject_key', 'subject:hook-1')
            ->count();
        $this->assertSame(1, $assignment);
    }

    public function test_cta_and_posting_time_experiments_route_to_distinct_slugs(): void
    {
        $workspaceId = $this->createWorkspace();

        $cta = app(ExperimentationEngine::class)->ensureExperiment($workspaceId, ExperimentType::Cta);
        $time = app(ExperimentationEngine::class)->ensureExperiment($workspaceId, ExperimentType::PostingTime);

        $this->assertSame('exp:cta', $cta->slug);
        $this->assertSame('exp:posting_time', $time->slug);
        $this->assertNotSame($cta->id, $time->id);
    }

    public function test_statistical_comparison_produces_narrative(): void
    {
        $workspaceId = $this->createWorkspace();
        $experiment = app(ExperimentationEngine::class)->ensureExperiment($workspaceId, ExperimentType::HookAb);

        $comparison = app(CompareExperimentAction::class)->execute($workspaceId, $experiment->id);

        $this->assertGreaterThan(0, $comparison->liftPercent);
        $this->assertGreaterThan(0, $comparison->confidence);
        $this->assertStringContainsString('outperforms', $comparison->narrative);
        $this->assertStringContainsString('confidence', $comparison->narrative);
    }

    public function test_assignment_idempotency_prevents_duplicate_rows(): void
    {
        $workspaceId = $this->createWorkspace();
        $engine = app(ExperimentationEngine::class);
        $experiment = $engine->ensureExperiment($workspaceId, ExperimentType::HookAb);

        $engine->assignVariant($workspaceId, ExperimentType::HookAb, 'subject:idempotent');
        $engine->assignVariant($workspaceId, ExperimentType::HookAb, 'subject:idempotent');

        $count = ExperimentResult::query()
            ->where('experiment_id', $experiment->id)
            ->where('result_type', ExperimentResultType::Assignment->value)
            ->where('subject_key', 'subject:idempotent')
            ->count();

        $this->assertSame(1, $count);
    }

    private function createWorkspace(): string
    {
        $workspaceId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Exp WS',
            'slug' => 'exp-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $workspaceId;
    }
}

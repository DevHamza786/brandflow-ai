<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Analytics\Services\EngagementTrackingService;
use App\Domains\Brand\Models\BrandProfile;
use App\Domains\Recommendations\Actions\GenerateRecommendationsAction;
use App\Domains\Recommendations\Enums\RecommendationType;
use App\Domains\Recommendations\Models\Recommendation;
use App\Domains\Recommendations\Services\RecommendationQueryService;
use App\Domains\Recommendations\Support\HookStyleClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RecommendationEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_hook_style_correlation_emits_evidence_backed_recommendation(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->createBrandProfile($workspaceId, ['SaaS founders'], ['Ask a bold question']);

        $tracking = app(EngagementTrackingService::class);

        for ($i = 0; $i < 4; $i++) {
            $tracking->recordPostEngagement(
                workspaceId: $workspaceId,
                entityType: 'scheduled_post',
                entityId: Str::uuid()->toString(),
                impressions: 3000,
                likes: 180,
                comments: 25,
                hookPerformance: [
                    'text' => 'Why do SaaS founders struggle with pipeline consistency?',
                    'overall' => 78.0,
                    'dimensions' => [
                        'curiosity_gap' => 82,
                        'specificity' => 75,
                        'clarity' => 80,
                        'audience_fit' => 85,
                    ],
                ],
            );
        }

        for ($i = 0; $i < 4; $i++) {
            $tracking->recordPostEngagement(
                workspaceId: $workspaceId,
                entityType: 'scheduled_post',
                entityId: Str::uuid()->toString(),
                impressions: 3000,
                likes: 12,
                comments: 1,
                hookPerformance: [
                    'text' => 'Start posting more content this week.',
                    'overall' => 74.0,
                    'dimensions' => [
                        'curiosity_gap' => 55,
                        'specificity' => 50,
                        'clarity' => 70,
                        'audience_fit' => 52,
                    ],
                ],
            );
        }

        $result = app(GenerateRecommendationsAction::class)->execute($workspaceId);

        $this->assertGreaterThan(0, $result->generatedCount);

        $styleRec = Recommendation::query()
            ->where('workspace_id', $workspaceId)
            ->where('type', RecommendationType::HookStyle->value)
            ->where('status', 'active')
            ->first();

        $this->assertNotNull($styleRec);
        $this->assertStringContainsString('question', strtolower($styleRec->summary));
        $this->assertIsArray($styleRec->evidence);
        $this->assertArrayHasKey('uplift_pct', $styleRec->evidence);
        $this->assertGreaterThanOrEqual(3, $styleRec->evidence['sample_size'] ?? 0);
        $this->assertStringContainsString('SaaS founders', (string) json_encode($styleRec->personalization_context));
    }

    public function test_weak_hook_lab_gap_triggers_rewrite_suggestion(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->createBrandProfile($workspaceId, ['operators'], []);

        app(EngagementTrackingService::class)->recordPostEngagement(
            workspaceId: $workspaceId,
            entityType: 'scheduled_post',
            entityId: Str::uuid()->toString(),
            impressions: 5000,
            likes: 400,
            comments: 40,
            hookPerformance: ['text' => 'What is the one metric founders ignore?', 'overall' => 90.0],
        );

        app(EngagementTrackingService::class)->recordPostEngagement(
            workspaceId: $workspaceId,
            entityType: 'scheduled_post',
            entityId: Str::uuid()->toString(),
            impressions: 5000,
            likes: 8,
            comments: 0,
            hookPerformance: [
                'text' => 'I built a perfect hook in the lab but it flopped live.',
                'overall' => 88.0,
            ],
        );

        app(GenerateRecommendationsAction::class)->execute($workspaceId);

        $weak = Recommendation::query()
            ->where('workspace_id', $workspaceId)
            ->where('type', RecommendationType::EngagementImprovement->value)
            ->where('summary', 'like', '%bottom quartile%')
            ->first();

        $this->assertNotNull($weak);
        $this->assertStringContainsString('rewrite', strtolower($weak->title));
    }

    public function test_personalization_differs_by_brand_profile(): void
    {
        $wsA = $this->createWorkspace();
        $wsB = $this->createWorkspace();
        $this->createBrandProfile($wsA, ['SaaS founders'], []);
        $this->createBrandProfile($wsB, ['enterprise CTOs'], []);

        $this->seedUniformSnapshots($wsA, 'Why do founders miss churn signals?');
        $this->seedUniformSnapshots($wsB, 'Why do CTOs miss security debt?');

        app(GenerateRecommendationsAction::class)->execute($wsA);
        app(GenerateRecommendationsAction::class)->execute($wsB);

        $ctxA = Recommendation::query()->where('workspace_id', $wsA)->value('personalization_context');
        $ctxB = Recommendation::query()->where('workspace_id', $wsB)->value('personalization_context');

        $this->assertNotEquals(
            $ctxA['audience_segments'] ?? null,
            $ctxB['audience_segments'] ?? null,
        );
    }

    public function test_recommendations_api_generate_and_list(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->seedUniformSnapshots($workspaceId, 'How can B2B teams fix activation?');

        $generate = $this->postJson('/api/v1/recommendations/generate', [], [
            'X-Workspace-Id' => $workspaceId,
        ]);

        $generate->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['generated_count', 'recommendations']]);

        $list = $this->getJson('/api/v1/recommendations', [
            'X-Workspace-Id' => $workspaceId,
        ]);

        $list->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['recommendations']]);

        $this->assertGreaterThan(0, count($list->json('data.recommendations')));
    }

    public function test_workspace_isolation_on_query(): void
    {
        $wsA = $this->createWorkspace();
        $wsB = $this->createWorkspace();
        $this->seedUniformSnapshots($wsA, 'Why do agencies lose retainers?');
        app(GenerateRecommendationsAction::class)->execute($wsA);

        $rows = app(RecommendationQueryService::class)->listActive($wsB);

        $this->assertSame([], $rows);
    }

    public function test_hook_style_classifier_detects_question(): void
    {
        $classifier = app(HookStyleClassifier::class);
        $this->assertSame(
            HookStyleClassifier::STYLE_QUESTION,
            $classifier->classify('Why do SaaS founders ignore churn until it is too late?'),
        );
    }

    private function createWorkspace(): string
    {
        $workspaceId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Rec WS',
            'slug' => 'rec-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $workspaceId;
    }

    /**
     * @param  list<string>  $segments
     * @param  list<string>  $hookPatterns
     */
    private function createBrandProfile(string $workspaceId, array $segments, array $hookPatterns): void
    {
        BrandProfile::query()->create([
            'workspace_id' => $workspaceId,
            'name' => 'Primary',
            'brand_voice' => 'Direct, operator-focused',
            'tone_profile' => ['primary' => 'direct', 'traits' => ['confident']],
            'target_audience' => [
                'summary' => 'Founders',
                'segments' => $segments,
            ],
            'preferred_ctas' => ['Book a call', 'Comment PLAYBOOK'],
            'preferred_hook_patterns' => $hookPatterns,
            'style_guidelines' => [],
            'memory_version' => 1,
            'is_primary' => true,
            'metadata' => [],
        ]);
    }

    private function seedUniformSnapshots(string $workspaceId, string $hookText): void
    {
        $tracking = app(EngagementTrackingService::class);
        for ($i = 0; $i < 5; $i++) {
            $tracking->recordPostEngagement(
                workspaceId: $workspaceId,
                entityType: 'scheduled_post',
                entityId: Str::uuid()->toString(),
                impressions: 2500,
                likes: 150 - ($i * 5),
                comments: 10,
                hookPerformance: [
                    'text' => $hookText,
                    'overall' => 75.0,
                    'dimensions' => [
                        'curiosity_gap' => 80,
                        'specificity' => 70,
                        'clarity' => 78,
                        'audience_fit' => 82,
                    ],
                ],
            );
        }
    }
}

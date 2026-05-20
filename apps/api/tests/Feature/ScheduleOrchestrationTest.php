<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Schedule\Models\ScheduledPost;
use App\Domains\Schedule\Services\SchedulerOrchestrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ScheduleOrchestrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_orchestration_claims_due_scheduled_rows_and_dispatches_publish_jobs(): void
    {
        Bus::fake();

        $workspaceId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Test Workspace',
            'slug' => 'test-'.substr($workspaceId, 0, 8),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $integrationId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('linkedin_integrations')->insert([
            'id' => $integrationId,
            'workspace_id' => $workspaceId,
            'provider' => 'linkedin',
            'linkedin_member_id' => 'm1',
            'access_token' => 'x',
            'refresh_token' => null,
            'token_expires_at' => now()->addHour(),
            'scopes' => json_encode([]),
            'metadata' => json_encode([]),
            'status' => 'connected',
            'connected_at' => now(),
            'last_synced_at' => null,
            'last_error' => null,
            'refresh_attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postId = (string) Str::uuid();
        ScheduledPost::query()->create([
            'id' => $postId,
            'workspace_id' => $workspaceId,
            'platform' => 'linkedin',
            'schedule_pattern' => 'once',
            'linkedin_integration_id' => $integrationId,
            'content' => 'Due slot',
            'publish_at' => now()->subMinute(),
            'scheduled_for' => now()->subMinute(),
            'timezone' => 'UTC',
            'status' => ScheduledPostStatus::Scheduled->value,
            'metadata' => [],
            'attempt_count' => 0,
        ]);

        $svc = app(SchedulerOrchestrationService::class);
        $result = $svc->processDuePosts($workspaceId, 10, $svc->newTraceId());

        $this->assertSame(1, $result->claimedCount);
        $this->assertCount(1, $result->dispatches);
        $this->assertTrue($result->dispatches[0]->dispatched);

        $fresh = ScheduledPost::query()->find($postId);
        $this->assertSame(ScheduledPostStatus::Queued->value, $fresh?->status);
        $this->assertNotNull($fresh?->execution_id);
    }

    /**
     * Double-orchestration on the same claimed row cannot occur because status moves off `scheduled`.
     */
    public function test_second_orchestration_tick_does_not_double_dispatch_same_row(): void
    {
        Bus::fake();

        $workspaceId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'WS',
            'slug' => 'ws-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $integrationId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('linkedin_integrations')->insert([
            'id' => $integrationId,
            'workspace_id' => $workspaceId,
            'provider' => 'linkedin',
            'linkedin_member_id' => 'm1',
            'access_token' => 'x',
            'refresh_token' => null,
            'token_expires_at' => now()->addHour(),
            'scopes' => json_encode([]),
            'metadata' => json_encode([]),
            'status' => 'connected',
            'connected_at' => now(),
            'last_synced_at' => null,
            'last_error' => null,
            'refresh_attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postId = (string) Str::uuid();
        ScheduledPost::query()->create([
            'id' => $postId,
            'workspace_id' => $workspaceId,
            'platform' => 'linkedin',
            'schedule_pattern' => 'once',
            'linkedin_integration_id' => $integrationId,
            'content' => 'Once',
            'publish_at' => now()->subMinute(),
            'scheduled_for' => now()->subMinute(),
            'timezone' => 'UTC',
            'status' => ScheduledPostStatus::Scheduled->value,
            'metadata' => [],
            'attempt_count' => 0,
        ]);

        $svc = app(SchedulerOrchestrationService::class);
        $svc->processDuePosts($workspaceId, 10, $svc->newTraceId());

        $second = $svc->processDuePosts($workspaceId, 10, $svc->newTraceId());

        $this->assertSame(0, $second->claimedCount);
    }
}

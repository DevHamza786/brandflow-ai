<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Schedule\Jobs\PublishLinkedInPostJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PublishLinkedInTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepts_publish_request_and_dispatches_job(): void
    {
        Queue::fake();

        $workspaceId = Str::uuid()->toString();
        $integrationId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Test Workspace',
            'slug' => 'test-'.substr($workspaceId, 0, 8),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('linkedin_integrations')->insert([
            'id' => $integrationId,
            'workspace_id' => $workspaceId,
            'provider' => IntegrationProvider::LinkedIn->value,
            'linkedin_member_id' => 'test-member',
            'access_token' => 'dummy',
            'refresh_token' => null,
            'token_expires_at' => now()->addHour(),
            'scopes' => json_encode(['w_member_social']),
            'metadata' => json_encode([]),
            'status' => IntegrationStatus::Connected->value,
            'connected_at' => now(),
            'last_synced_at' => null,
            'last_error' => null,
            'refresh_attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson(
            '/api/v1/publish/linkedin',
            [
                'linkedin_integration_id' => $integrationId,
                'content' => 'Hello from PBOS publish test.',
            ],
            [
                'X-Workspace-Id' => $workspaceId,
                'Accept' => 'application/json',
            ],
        );

        $response->assertStatus(202);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', 'queued');

        Queue::assertPushed(PublishLinkedInPostJob::class, function (PublishLinkedInPostJob $job) use ($workspaceId): bool {
            return $job->workspaceId === $workspaceId;
        });
    }

    public function test_future_publish_stays_scheduled_without_immediate_publish_job(): void
    {
        Queue::fake();

        $workspaceId = Str::uuid()->toString();
        $integrationId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Test Workspace',
            'slug' => 'test-'.substr($workspaceId, 0, 8),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('linkedin_integrations')->insert([
            'id' => $integrationId,
            'workspace_id' => $workspaceId,
            'provider' => IntegrationProvider::LinkedIn->value,
            'linkedin_member_id' => 'test-member',
            'access_token' => 'dummy',
            'refresh_token' => null,
            'token_expires_at' => now()->addHour(),
            'scopes' => json_encode(['w_member_social']),
            'metadata' => json_encode([]),
            'status' => IntegrationStatus::Connected->value,
            'connected_at' => now(),
            'last_synced_at' => null,
            'last_error' => null,
            'refresh_attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson(
            '/api/v1/publish/linkedin',
            [
                'linkedin_integration_id' => $integrationId,
                'content' => 'Future post',
                'scheduled_for' => now()->addMinutes(5)->toIso8601String(),
            ],
            [
                'X-Workspace-Id' => $workspaceId,
                'Accept' => 'application/json',
            ],
        );

        $response->assertStatus(202);
        $response->assertJsonPath('data.status', 'scheduled');

        Queue::assertNotPushed(PublishLinkedInPostJob::class);
    }

    public function test_rejects_missing_workspace_header(): void
    {
        $response = $this->postJson('/api/v1/publish/linkedin', [
            'linkedin_integration_id' => Str::uuid()->toString(),
            'content' => 'x',
        ]);

        $response->assertStatus(400);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ScheduledPostsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_scheduled_posts_for_workspace(): void
    {
        $workspaceId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'W',
            'slug' => 'w-'.substr($workspaceId, 0, 8),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/scheduled-posts', [
            'X-Workspace-Id' => $workspaceId,
            'Accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.in_flight', false);
        $response->assertJsonPath('data.scheduled_posts', []);
    }

    public function test_requires_workspace_header(): void
    {
        $this->getJson('/api/v1/scheduled-posts')->assertStatus(400);
    }
}

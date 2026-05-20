<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Shared\Services\WorkspaceBootstrapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class WorkspaceBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_local_workspace_record_is_idempotent_under_repeat_calls(): void
    {
        $this->app['env'] = 'local';
        config(['pbos.workspace.local_auto_bootstrap' => true]);

        $workspaceId = '9bb59c64-347a-48b3-b010-e41b5cdc6f4d';
        $service = app(WorkspaceBootstrapService::class);

        $service->ensureLocalWorkspaceRecord($workspaceId);
        $service->ensureLocalWorkspaceRecord($workspaceId);

        $this->assertSame(
            1,
            DB::table('workspaces')->where('id', $workspaceId)->count(),
        );
        $this->assertDatabaseHas('workspaces', [
            'id' => $workspaceId,
            'name' => 'Development workspace',
        ]);
    }

    public function test_resolve_workspace_header_does_not_duplicate_existing_workspace(): void
    {
        $this->app['env'] = 'local';
        config(['pbos.workspace.local_auto_bootstrap' => true]);

        $workspaceId = (string) Str::uuid();
        DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Pre-seeded',
            'slug' => 'pre-seeded-'.substr($workspaceId, 0, 8),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $this->withHeader('X-Workspace-Id', $workspaceId)
            ->getJson('/api/v1/optimization/loops')
            ->assertOk();

        $this->assertSame(
            1,
            DB::table('workspaces')->where('id', $workspaceId)->count(),
        );
    }
}

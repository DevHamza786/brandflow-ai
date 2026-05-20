<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Shared\Services\WorkspaceBootstrapService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ensures the UUID used by `apps/web/.env` (`VITE_DEFAULT_WORKSPACE_ID`) exists in `workspaces`,
 * so LinkedIn OAuth and other workspace-scoped flows do not hit FK errors in local dev.
 */
final class DevDefaultWorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $id = (string) env(
            'PBOS_DEV_WORKSPACE_ID',
            '9bb59c64-347a-48b3-b010-e41b5cdc6f4d',
        );

        if (! Str::isUuid($id)) {
            $this->command?->warn('PBOS_DEV_WORKSPACE_ID is not a valid UUID; skipping DevDefaultWorkspaceSeeder.');

            return;
        }

        $existed = DB::table('workspaces')->where('id', $id)->exists();
        app(WorkspaceBootstrapService::class)->ensureLocalWorkspaceRecord($id);

        if ($this->command && ! $existed && DB::table('workspaces')->where('id', $id)->exists()) {
            $slug = DB::table('workspaces')->where('id', $id)->value('slug');
            $this->command->info("Dev workspace [{$id}] ready (slug: {$slug}).");
        }
    }
}

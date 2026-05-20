<?php

declare(strict_types=1);

namespace App\Domains\Shared\Services;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Upserts a minimal `workspaces` row in local dev to satisfy FK constraints
 * when the SPA uses a fixed UUID before any formal tenant provisioning exists.
 */
final class WorkspaceBootstrapService
{
    public function ensureLocalWorkspaceRecord(string $workspaceId): void
    {
        if (! app()->environment('local')) {
            return;
        }

        if (! Str::isUuid($workspaceId)) {
            return;
        }

        if (! (bool) config('pbos.workspace.local_auto_bootstrap', true)) {
            return;
        }

        $slugSuffix = substr(str_replace('-', '', $workspaceId), 0, 12);
        $slug = 'dev-ws-'.$slugSuffix;
        if (DB::table('workspaces')->where('slug', $slug)->where('id', '!=', $workspaceId)->exists()) {
            $slug .= '-'.substr(uniqid('', true), -4);
        }

        $now = now();
        $row = [
            'id' => $workspaceId,
            'name' => 'Development workspace',
            'slug' => $slug,
            'plan_id' => null,
            'settings' => json_encode([], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        // Atomic upsert — safe under concurrent SPA requests (React Query fan-out).
        try {
            DB::table('workspaces')->upsert(
                [$row],
                ['id'],
                ['name', 'slug', 'plan_id', 'settings', 'updated_at', 'deleted_at'],
            );
        } catch (UniqueConstraintViolationException $e) {
            if (! $this->isSlugUniqueViolation($e)) {
                throw $e;
            }

            $row['slug'] = $slug.'-'.substr(uniqid('', true), -4);
            DB::table('workspaces')->upsert(
                [$row],
                ['id'],
                ['name', 'slug', 'plan_id', 'settings', 'updated_at', 'deleted_at'],
            );
        }
    }

    private function isSlugUniqueViolation(UniqueConstraintViolationException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'workspaces_slug_unique')
            || str_contains($message, 'slug');
    }

    public function ensureConfiguredDevWorkspace(): void
    {
        $id = (string) config('pbos.dev_workspace_id');
        if (! Str::isUuid($id)) {
            return;
        }

        $this->ensureLocalWorkspaceRecord($id);
    }
}

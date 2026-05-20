<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workspace-scoped OAuth integrations (LinkedIn first; provider column for multi-platform).
 *
 * access_token / refresh_token are encrypted at the application layer (Laravel encrypted cast).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linkedin_integrations', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('provider', 32)->default('linkedin');

            $table->string('linkedin_member_id', 128)->nullable();

            /** @see LinkedInIntegration model — encrypted cast */
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();

            $table->timestampTz('token_expires_at')->nullable();

            $table->jsonb('scopes')->default('[]');
            $table->jsonb('metadata')->default('{}');

            $table->string('status', 32)->default('pending');

            $table->timestampTz('connected_at')->nullable();
            $table->timestampTz('last_synced_at')->nullable();

            $table->text('last_error')->nullable();
            $table->unsignedSmallInteger('refresh_attempts')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'provider']);
            $table->index(['workspace_id', 'status']);
            $table->index(['provider', 'status']);
            $table->index('token_expires_at');
            $table->index('linkedin_member_id');

            $table->unique(
                ['workspace_id', 'provider', 'linkedin_member_id'],
                'linkedin_integrations_workspace_provider_member_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_integrations');
    }
};

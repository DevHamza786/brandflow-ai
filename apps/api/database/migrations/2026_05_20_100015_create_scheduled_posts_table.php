<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scheduled LinkedIn publishes (calendar slots).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_posts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('content_item_id')
                ->constrained('content_items')
                ->cascadeOnDelete();

            $table->foreignUuid('content_version_id')
                ->nullable()
                ->constrained('content_versions')
                ->nullOnDelete();

            $table->timestamp('publish_at');
            $table->string('timezone', 64)->default('UTC');

            // scheduled | queued | publishing | published | failed | cancelled
            $table->string('status', 32)->default('scheduled');

            // LinkedIn account UUID (integrations table added later).
            $table->uuid('linkedin_account_id')->nullable();

            $table->string('linkedin_urn')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();

            $table->unsignedTinyInteger('attempt_count')->default(0);

            $table->jsonb('error')->nullable();
            $table->jsonb('metadata')->default('{}');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'publish_at']);
            $table->index(['workspace_id', 'status', 'publish_at']);
            $table->index(['content_item_id', 'status']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            // Dispatcher hot path: due slots that are still scheduled.
            DB::statement(
                "CREATE INDEX idx_scheduled_posts_due ON scheduled_posts (workspace_id, publish_at) WHERE status IN ('scheduled', 'queued') AND deleted_at IS NULL"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_scheduled_posts_due');
        }

        Schema::dropIfExists('scheduled_posts');
    }
};

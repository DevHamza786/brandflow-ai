<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Links scheduled posts to LinkedIn integrations + generated outputs; optional standalone content.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table): void {
            if (Schema::hasColumn('scheduled_posts', 'linkedin_integration_id')) {
                return;
            }

            $table->foreignUuid('linkedin_integration_id')
                ->nullable()
                ->after('workspace_id')
                ->constrained('linkedin_integrations')
                ->nullOnDelete();

            $table->foreignUuid('generated_output_id')
                ->nullable()
                ->after('linkedin_integration_id')
                ->constrained('generated_outputs')
                ->nullOnDelete();

            $table->text('content')->nullable();

            $table->string('provider_post_id', 512)->nullable();
            $table->jsonb('error_details')->nullable();

            // Canonical schedule time for future cron / analytics (mirrors publish_at when set).
            $table->timestampTz('scheduled_for')->nullable();

            $table->index(['workspace_id', 'linkedin_integration_id']);
            $table->index(['workspace_id', 'generated_output_id']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('UPDATE scheduled_posts SET scheduled_for = publish_at::timestamptz WHERE scheduled_for IS NULL');
        } else {
            DB::table('scheduled_posts')->whereNull('scheduled_for')->update([
                'scheduled_for' => DB::raw('publish_at'),
            ]);
        }

        if (Schema::hasColumn('scheduled_posts', 'content_item_id')) {
            try {
                Schema::table('scheduled_posts', function (Blueprint $table): void {
                    $table->dropForeign(['content_item_id']);
                });
            } catch (Throwable) {
                // ignore if naming differs
            }
            DB::statement('ALTER TABLE scheduled_posts ALTER COLUMN content_item_id DROP NOT NULL');
            Schema::table('scheduled_posts', function (Blueprint $table): void {
                $table->foreign('content_item_id')
                    ->references('id')
                    ->on('content_items')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('scheduled_posts', 'linkedin_integration_id')) {
                return;
            }

            $table->dropForeign(['linkedin_integration_id']);
            $table->dropForeign(['generated_output_id']);
            $table->dropIndex(['workspace_id', 'linkedin_integration_id']);
            $table->dropIndex(['workspace_id', 'generated_output_id']);

            $table->dropColumn([
                'linkedin_integration_id',
                'generated_output_id',
                'content',
                'provider_post_id',
                'error_details',
                'scheduled_for',
            ]);
        });
    }
};

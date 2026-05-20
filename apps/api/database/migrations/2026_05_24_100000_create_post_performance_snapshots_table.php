<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Denormalized post / hook performance observations for rollups, dashboards, and future ML loops.
 *
 * Partition strategy (later): range partition on `observed_at` monthly — PROJECT_ARCHITECTURE.md §3.5.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_performance_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('entity_type', 64);
            $table->uuid('entity_id');

            $table->string('provider_post_id', 512)->nullable();

            $table->timestampTz('posted_at')->nullable();
            $table->timestampTz('observed_at');

            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->unsignedBigInteger('reposts')->default(0);
            $table->unsignedBigInteger('saves')->default(0);

            $table->decimal('engagement_rate', 14, 8)->nullable();
            $table->decimal('normalized_engagement', 14, 8)->nullable();

            /** Hook Lab / variant context at observation time (text, dimensions, agent scores). */
            $table->jsonb('hook_performance')->nullable();

            /** Structured content signals (pillar, length bucket, format) — dashboard / ML later. */
            $table->jsonb('content_features')->nullable();

            /** Reserved for embeddings, bandits, RL state — keep append-only at API boundary. */
            $table->jsonb('ml_features')->nullable();

            $table->jsonb('metadata')->default('{}');

            $table->timestamps();

            $table->index(['workspace_id', 'observed_at']);
            $table->index(['workspace_id', 'entity_type', 'entity_id', 'observed_at']);
            $table->index(['workspace_id', 'posted_at']);
            $table->index('provider_post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_performance_snapshots');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only orchestration timeline for scheduling (analytics-ready, no dashboards in this phase).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_execution_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('scheduled_post_id')
                ->nullable()
                ->constrained('scheduled_posts')
                ->nullOnDelete();

            $table->string('phase', 64)->index();

            $table->string('trace_id', 64)->nullable()->index();

            $table->json('payload')->default('{}');

            $table->timestampTz('occurred_at')->useCurrent();

            $table->index(['workspace_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_execution_events');
    }
};

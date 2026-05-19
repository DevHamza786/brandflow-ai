<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only product analytics event stream (partition-ready).
 *
 * At scale: partition by range (created_at) monthly — see PROJECT_ARCHITECTURE.md §3.5.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            // e.g. content.published, hook.scored, agent.run_completed
            $table->string('event_type', 128);

            $table->string('entity_type', 64)->nullable();
            $table->uuid('entity_id')->nullable();

            $table->jsonb('properties')->default('{}');

            // Business time of the event (may differ from created_at).
            $table->timestamp('occurred_at')->useCurrent();

            $table->string('idempotency_key')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('session_id')->nullable();

            $table->timestamps();

            // Primary dashboard / rollup ingestion paths
            $table->index(['workspace_id', 'created_at']);
            $table->index(['workspace_id', 'event_type', 'created_at']);
            $table->index(['workspace_id', 'occurred_at']);
            $table->index(['entity_type', 'entity_id', 'created_at']);

            $table->unique(['workspace_id', 'idempotency_key']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            // Query filters on JSON properties (use sparingly; prefer rollups for hot paths).
            DB::statement(
                'CREATE INDEX idx_analytics_events_properties_gin ON analytics_events USING gin (properties jsonb_path_ops)'
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_analytics_events_properties_gin');
        }

        Schema::dropIfExists('analytics_events');
    }
};

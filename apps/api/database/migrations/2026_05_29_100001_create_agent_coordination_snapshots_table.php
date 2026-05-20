<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-step coordination audit trail (routing, context, dispatch, recovery).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_coordination_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('agent_coordination_id')
                ->constrained('agent_coordinations')
                ->cascadeOnDelete();

            $table->string('snapshot_type', 64);
            $table->unsignedInteger('cycle_number')->default(0);

            $table->string('role_slug', 64)->nullable();
            $table->string('task_type', 64)->nullable();
            $table->string('agent_slug', 64)->nullable();
            $table->string('routed_agent_slug', 64)->nullable();
            $table->string('handler_type', 32)->nullable();

            $table->string('status', 32)->default('pending');

            $table->jsonb('context_refs')->default('{}');
            $table->jsonb('payload')->default('{}');
            $table->jsonb('error')->nullable();

            $table->string('idempotency_key', 128)->nullable();
            $table->string('trace_id', 64)->nullable();

            $table->foreignUuid('agent_run_id')
                ->nullable()
                ->constrained('agent_runs')
                ->nullOnDelete();

            $table->unsignedInteger('priority')->default(100);
            $table->unsignedInteger('duration_ms')->nullable();

            $table->timestamps();

            $table->unique(['workspace_id', 'idempotency_key']);
            $table->index(['agent_coordination_id', 'cycle_number', 'created_at']);
            $table->index(['workspace_id', 'snapshot_type', 'status']);
            $table->index(['workspace_id', 'role_slug', 'created_at']);
            $table->index(['agent_run_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_coordination_snapshots');
    }
};

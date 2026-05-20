<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workspace multi-agent coordination sessions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_coordinations', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('status', 32)->default('active');
            $table->string('coordination_mode', 64)->default('sequential');

            $table->string('correlation_key', 128);

            $table->unsignedInteger('current_cycle')->default(0);

            $table->foreignUuid('workflow_run_id')
                ->nullable()
                ->constrained('workflow_runs')
                ->nullOnDelete();

            $table->foreignUuid('optimization_loop_id')
                ->nullable()
                ->constrained('optimization_loops')
                ->nullOnDelete();

            $table->foreignUuid('autonomous_workflow_id')
                ->nullable()
                ->constrained('autonomous_workflows')
                ->nullOnDelete();

            $table->jsonb('shared_context')->default('{}');
            $table->jsonb('config')->default('{}');
            $table->jsonb('ml_state')->default('{}');
            $table->jsonb('metadata')->default('{}');

            $table->string('lock_token', 64)->nullable();
            $table->timestampTz('locked_at')->nullable();

            $table->timestampTz('started_at');
            $table->timestampTz('last_run_at')->nullable();
            $table->timestampTz('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['workspace_id', 'correlation_key']);
            $table->index(['workspace_id', 'status', 'last_run_at']);
            $table->index(['workspace_id', 'coordination_mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_coordinations');
    }
};

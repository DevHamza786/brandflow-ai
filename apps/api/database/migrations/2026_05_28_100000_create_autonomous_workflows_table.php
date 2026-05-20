<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workspace autonomous posting workflows — observe → optimize → decide → (future) publish.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autonomous_workflows', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('status', 32)->default('active');
            $table->string('mode', 32)->default('suggest');

            $table->string('correlation_key', 128);

            $table->unsignedInteger('current_cycle')->default(0);

            $table->foreignUuid('optimization_loop_id')
                ->nullable()
                ->constrained('optimization_loops')
                ->nullOnDelete();

            $table->foreignUuid('workflow_run_id')
                ->nullable()
                ->constrained('workflow_runs')
                ->nullOnDelete();

            $table->jsonb('config')->default('{}');
            $table->jsonb('ml_state')->default('{}');
            $table->jsonb('metadata')->default('{}');

            $table->boolean('manual_override_enabled')->default(true);
            $table->boolean('autonomous_execution_enabled')->default(false);

            $table->timestampTz('locked_at')->nullable();
            $table->string('lock_token', 64)->nullable();

            $table->timestampTz('started_at');
            $table->timestampTz('last_run_at')->nullable();
            $table->timestampTz('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['workspace_id', 'correlation_key']);
            $table->index(['workspace_id', 'status', 'mode']);
            $table->index(['workspace_id', 'last_run_at']);
            $table->index(['locked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autonomous_workflows');
    }
};

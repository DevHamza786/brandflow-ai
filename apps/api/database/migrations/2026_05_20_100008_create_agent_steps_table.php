<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-step audit trail for agent runs (tools, LLM calls, human gates).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('agent_run_id')
                ->constrained('agent_runs')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('step_index');

            // tool | llm | human_gate
            $table->string('step_type', 32);

            $table->string('tool_slug')->nullable();

            // pending | running | completed | failed | skipped
            $table->string('status', 32)->default('pending');

            $table->jsonb('payload')->default('{}');
            $table->jsonb('result')->nullable();
            $table->jsonb('usage')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['agent_run_id', 'step_index']);
            $table->index(['agent_run_id', 'status']);
            $table->index(['workspace_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_steps');
    }
};

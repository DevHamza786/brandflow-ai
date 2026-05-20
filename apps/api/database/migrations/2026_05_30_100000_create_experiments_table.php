<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workspace-scoped experimentation definitions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiments', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('slug', 128);
            $table->string('name');
            $table->string('experiment_type', 64);
            $table->string('status', 32)->default('draft');

            $table->text('hypothesis')->nullable();

            $table->jsonb('config')->default('{}');
            $table->jsonb('ml_state')->default('{}');
            $table->jsonb('metadata')->default('{}');

            $table->foreignUuid('optimization_loop_id')
                ->nullable()
                ->constrained('optimization_loops')
                ->nullOnDelete();

            $table->foreignUuid('workflow_blueprint_id')
                ->nullable()
                ->constrained('workflow_blueprints')
                ->nullOnDelete();

            $table->foreignUuid('agent_coordination_id')
                ->nullable()
                ->constrained('agent_coordinations')
                ->nullOnDelete();

            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('ended_at')->nullable();

            $table->timestamps();

            $table->unique(['workspace_id', 'slug']);
            $table->index(['workspace_id', 'experiment_type', 'status']);
            $table->index(['workspace_id', 'status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiments');
    }
};

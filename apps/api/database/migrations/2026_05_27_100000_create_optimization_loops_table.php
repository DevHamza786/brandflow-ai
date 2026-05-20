<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workspace optimization loops — adaptive generate → publish → observe → optimize cycles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optimization_loops', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('loop_type', 64);
            $table->string('status', 32)->default('active');

            $table->string('correlation_key', 128);

            $table->unsignedInteger('current_cycle')->default(0);

            $table->jsonb('config')->default('{}');
            $table->jsonb('ml_state')->default('{}');
            $table->jsonb('metadata')->default('{}');

            $table->timestampTz('started_at');
            $table->timestampTz('last_run_at')->nullable();
            $table->timestampTz('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['workspace_id', 'correlation_key']);
            $table->index(['workspace_id', 'loop_type', 'status']);
            $table->index(['workspace_id', 'last_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optimization_loops');
    }
};

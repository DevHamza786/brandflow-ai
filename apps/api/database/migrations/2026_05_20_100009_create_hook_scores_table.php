<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hook Lab scores and variants per content version.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hook_scores', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('content_version_id')
                ->constrained('content_versions')
                ->cascadeOnDelete();

            $table->foreignUuid('agent_run_id')
                ->nullable()
                ->constrained('agent_runs')
                ->nullOnDelete();

            $table->decimal('score', 5, 2);

            $table->jsonb('dimensions')->default('{}');
            $table->jsonb('variants')->nullable();
            $table->jsonb('suggestions')->nullable();

            $table->string('model')->nullable();
            $table->string('prompt_version')->nullable();
            $table->string('trace_id')->nullable();

            $table->jsonb('metadata')->default('{}');

            $table->timestamps();

            $table->index(['content_version_id', 'created_at']);
            $table->index(['workspace_id', 'created_at']);
            $table->index('agent_run_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hook_scores');
    }
};

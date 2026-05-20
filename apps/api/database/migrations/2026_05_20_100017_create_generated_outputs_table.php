<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unified persistence for AI workflow / agent generated artifacts.
 *
 * Analytics: lead indexes with workspace_id; filter by type, status, created_at.
 * Retrieval: optional GIN on metadata for keyed lookups (embedding_id, memory_chunk_ids).
 * Idempotency: one row per (workspace, agent_run, type) when agent_run_id is set.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_outputs', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('workflow_run_id')
                ->nullable()
                ->constrained('workflow_runs')
                ->nullOnDelete();

            $table->foreignUuid('agent_run_id')
                ->nullable()
                ->constrained('agent_runs')
                ->nullOnDelete();

            $table->foreignUuid('content_version_id')
                ->nullable()
                ->constrained('content_versions')
                ->nullOnDelete();

            // hook | profile | analytics | carousel | reply | competitor | custom
            $table->string('type', 64);

            $table->string('provider', 32)->nullable();
            $table->string('model')->nullable();
            $table->string('prompt_version', 64)->nullable();

            $table->jsonb('input')->default('{}');
            $table->jsonb('output')->nullable();
            $table->jsonb('scores')->default('{}');
            $table->jsonb('metadata')->default('{}');

            // pending | processing | completed | failed | superseded
            $table->string('status', 32)->default('pending');

            $table->timestamps();

            // Tenant-scoped list + time-series analytics
            $table->index(['workspace_id', 'created_at']);
            $table->index(['workspace_id', 'type', 'status', 'created_at']);
            $table->index(['workspace_id', 'status', 'created_at']);

            // Workflow / agent correlation
            $table->index(['workspace_id', 'workflow_run_id', 'created_at']);
            $table->index(['workspace_id', 'agent_run_id', 'created_at']);
            $table->index(['workspace_id', 'content_version_id', 'created_at']);

            // Provider / model cost & quality analytics
            $table->index(['workspace_id', 'provider', 'model', 'created_at']);

            // Async idempotency: one persisted output per agent run + output type
            $table->unique(
                ['workspace_id', 'agent_run_id', 'type'],
                'generated_outputs_ws_agent_type_unique'
            );
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE INDEX generated_outputs_metadata_gin ON generated_outputs USING GIN (metadata jsonb_path_ops)'
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS generated_outputs_metadata_gin');
        }

        Schema::dropIfExists('generated_outputs');
    }
};

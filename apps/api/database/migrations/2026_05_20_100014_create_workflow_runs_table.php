<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workflow execution instances with mutable context.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('workflow_id')
                ->constrained('workflows')
                ->cascadeOnDelete();

            // queued | running | awaiting_approval | completed | failed | cancelled
            $table->string('status', 32)->default('queued');

            $table->jsonb('context')->default('{}');
            $table->jsonb('error')->nullable();

            $table->string('current_step_id')->nullable();

            $table->string('idempotency_key')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('triggered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['workspace_id', 'idempotency_key']);
            $table->index(['workspace_id', 'status', 'created_at']);
            $table->index(['workflow_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_runs');
    }
};

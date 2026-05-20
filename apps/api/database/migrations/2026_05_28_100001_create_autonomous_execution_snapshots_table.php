<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-cycle autonomous decisions (confidence, evidence, idempotency, ML-ready).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autonomous_execution_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('autonomous_workflow_id')
                ->constrained('autonomous_workflows')
                ->cascadeOnDelete();

            $table->unsignedInteger('cycle_number');
            $table->string('status', 48);
            $table->string('decision_type', 64);
            $table->string('engine', 64);
            $table->string('focus', 64);

            $table->unsignedSmallInteger('score')->default(0);
            $table->decimal('confidence', 5, 4)->nullable();

            $table->string('title', 255);
            $table->text('summary');
            $table->text('rationale')->nullable();
            $table->string('blocked_reason', 255)->nullable();

            $table->jsonb('decision_payload')->default('{}');
            $table->jsonb('evidence')->default('{}');
            $table->jsonb('action_payload')->default('{}');
            $table->jsonb('personalization_context')->default('{}');
            $table->jsonb('ml_features')->default('{}');

            $table->uuid('recommendation_id')->nullable();
            $table->uuid('scheduled_post_id')->nullable();
            $table->uuid('generated_output_id')->nullable();

            $table->timestampTz('captured_at');
            $table->string('idempotency_key', 128);

            $table->timestamps();

            $table->unique('idempotency_key');
            $table->index(['workspace_id', 'captured_at']);
            $table->index(['autonomous_workflow_id', 'cycle_number']);
            $table->index(['workspace_id', 'status', 'decision_type']);
            $table->index(['workspace_id', 'confidence']);
            $table->unique(
                ['autonomous_workflow_id', 'cycle_number', 'focus'],
                'autonomous_execution_snapshots_workflow_cycle_focus_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autonomous_execution_snapshots');
    }
};

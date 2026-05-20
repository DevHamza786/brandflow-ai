<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-cycle optimization observations (historical comparison, scoring, ML-ready).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optimization_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('optimization_loop_id')
                ->constrained('optimization_loops')
                ->cascadeOnDelete();

            $table->unsignedInteger('cycle_number');
            $table->string('status', 32)->default('proposed');
            $table->string('engine', 64);
            $table->string('focus', 64);

            $table->unsignedSmallInteger('score')->default(0);
            $table->decimal('confidence', 5, 4)->nullable();

            $table->string('title', 255);
            $table->text('summary');
            $table->text('rationale')->nullable();

            $table->jsonb('baseline_metrics')->default('{}');
            $table->jsonb('observed_metrics')->default('{}');
            $table->jsonb('delta_metrics')->default('{}');
            $table->jsonb('evidence')->default('{}');
            $table->jsonb('action_payload')->default('{}');
            $table->jsonb('personalization_context')->default('{}');
            $table->jsonb('ml_features')->default('{}');

            $table->timestampTz('captured_at');
            $table->string('idempotency_key', 128)->nullable();

            $table->timestamps();

            $table->index(['workspace_id', 'captured_at']);
            $table->index(['optimization_loop_id', 'cycle_number']);
            $table->index(['workspace_id', 'engine', 'status']);
            $table->index(['workspace_id', 'score']);
            $table->unique(['optimization_loop_id', 'cycle_number', 'focus'], 'optimization_snapshots_loop_cycle_focus_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optimization_snapshots');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assignments, observations, and statistical comparisons (audit-safe).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiment_results', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('experiment_id')
                ->constrained('experiments')
                ->cascadeOnDelete();

            $table->foreignUuid('experiment_variant_id')
                ->nullable()
                ->constrained('experiment_variants')
                ->nullOnDelete();

            $table->string('result_type', 32);
            $table->string('entity_type', 64)->nullable();
            $table->uuid('entity_id')->nullable();

            $table->string('subject_key', 128)->nullable();

            $table->jsonb('metrics')->default('{}');
            $table->jsonb('statistical_summary')->default('{}');

            $table->string('idempotency_key', 128)->nullable();
            $table->string('trace_id', 64)->nullable();

            $table->timestamps();

            $table->unique(['workspace_id', 'idempotency_key']);
            $table->index(['experiment_id', 'result_type', 'created_at']);
            $table->index(['workspace_id', 'subject_key']);
            $table->index(['experiment_variant_id', 'result_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiment_results');
    }
};

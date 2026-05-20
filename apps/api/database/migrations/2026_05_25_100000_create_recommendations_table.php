<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workspace-scoped optimization recommendations (analytics-driven, ML-ready).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('type', 64);
            $table->string('status', 32)->default('active');
            $table->string('source', 64);

            /** Dedup / supersede key (e.g. hook_style:question). */
            $table->string('correlation_key', 128);

            $table->string('title', 255);
            $table->text('summary');
            $table->text('rationale')->nullable();

            $table->unsignedSmallInteger('score')->default(0);
            $table->decimal('confidence', 5, 4)->nullable();

            $table->jsonb('evidence')->default('{}');
            $table->jsonb('personalization_context')->default('{}');
            $table->jsonb('action_payload')->default('{}');
            $table->jsonb('ml_state')->default('{}');

            $table->timestampTz('generated_at');
            $table->timestampTz('valid_from')->nullable();
            $table->timestampTz('valid_until')->nullable();

            /** Optional lineage — no FK (avoids PG self-ref during create; set in app layer). */
            $table->uuid('superseded_by_id')->nullable();

            $table->string('idempotency_key', 128)->nullable();

            $table->timestamps();

            $table->index(['workspace_id', 'correlation_key']);
            $table->index(['workspace_id', 'type', 'status']);
            $table->index(['workspace_id', 'generated_at']);
            $table->index(['workspace_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};

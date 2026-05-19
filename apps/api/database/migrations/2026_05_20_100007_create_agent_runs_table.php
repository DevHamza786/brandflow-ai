<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent execution records (Hook, Profile, Analytics, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            // hook | profile | analytics | competitor | reply | carousel
            $table->string('slug', 32);

            // queued | running | completed | failed | cancelled
            $table->string('status', 32)->default('queued');

            $table->jsonb('input')->default('{}');
            $table->jsonb('options')->default('{}');
            $table->jsonb('output')->nullable();
            $table->jsonb('error')->nullable();

            $table->string('trace_id')->nullable();
            $table->string('idempotency_key')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['workspace_id', 'idempotency_key']);
            $table->index(['workspace_id', 'slug', 'status']);
            $table->index(['workspace_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_runs');
    }
};

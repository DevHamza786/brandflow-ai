<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workflow DAG definitions (multi-agent + human approval orchestration).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('slug', 128);
            $table->string('name');

            // DAG JSON: steps, depends_on, types (agent | job | human_gate)
            $table->jsonb('definition')->default('{}');

            $table->unsignedInteger('version')->default(1);

            $table->boolean('is_active')->default(true);

            $table->jsonb('metadata')->default('{}');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['workspace_id', 'slug']);
            $table->index(['workspace_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nodes within a workflow blueprint graph (agent, delay, condition, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_nodes', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('workflow_blueprint_id')
                ->constrained('workflow_blueprints')
                ->cascadeOnDelete();

            $table->string('node_key', 64);
            $table->string('node_type', 64);
            $table->string('label')->nullable();

            $table->jsonb('config')->default('{}');
            $table->jsonb('position')->default('{}');

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['workflow_blueprint_id', 'node_key']);
            $table->index(['workspace_id', 'node_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_nodes');
    }
};

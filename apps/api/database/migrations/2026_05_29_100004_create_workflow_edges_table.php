<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Directed edges between workflow blueprint nodes (conditional branching).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_edges', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('workflow_blueprint_id')
                ->constrained('workflow_blueprints')
                ->cascadeOnDelete();

            $table->string('from_node_key', 64);
            $table->string('to_node_key', 64);

            $table->string('edge_type', 32)->default('default');

            $table->jsonb('condition')->nullable();
            $table->jsonb('metadata')->default('{}');

            $table->timestamps();

            $table->unique([
                'workflow_blueprint_id',
                'from_node_key',
                'to_node_key',
                'edge_type',
            ]);
            $table->index(['workflow_blueprint_id', 'from_node_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_edges');
    }
};

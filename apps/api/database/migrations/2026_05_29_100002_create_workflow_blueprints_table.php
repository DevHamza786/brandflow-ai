<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visual/no-code compatible workflow blueprint definitions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_blueprints', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('slug', 128);
            $table->string('name');
            $table->string('status', 32)->default('draft');

            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(false);

            $table->string('blueprint_type', 64)->default('multi_agent');

            $table->jsonb('config')->default('{}');
            $table->jsonb('ml_state')->default('{}');
            $table->jsonb('metadata')->default('{}');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['workspace_id', 'slug', 'version']);
            $table->index(['workspace_id', 'is_active', 'status']);
            $table->index(['workspace_id', 'blueprint_type']);
        });

        Schema::table('agent_coordinations', function (Blueprint $table): void {
            $table->foreignUuid('workflow_blueprint_id')
                ->nullable()
                ->after('workflow_run_id')
                ->constrained('workflow_blueprints')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agent_coordinations', function (Blueprint $table): void {
            $table->dropForeign(['workflow_blueprint_id']);
        });

        Schema::dropIfExists('workflow_blueprints');
    }
};

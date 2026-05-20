<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workspace writing samples — style extraction + future vector embeddings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_samples', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('brand_profile_id')
                ->nullable()
                ->constrained('brand_profiles')
                ->nullOnDelete();

            $table->text('content');

            // manual | linkedin_post | content_import | agent_output | email
            $table->string('source_type', 64)->default('manual');

            $table->jsonb('metadata')->default('{}');

            // True when normalized_style_data is ready for embedding pipeline.
            $table->boolean('embedding_ready')->default(false);

            $table->jsonb('normalized_style_data')->default('{}');

            $table->timestamps();

            $table->index(['workspace_id', 'source_type']);
            $table->index(['workspace_id', 'embedding_ready', 'updated_at']);
            $table->index(['brand_profile_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_samples');
    }
};

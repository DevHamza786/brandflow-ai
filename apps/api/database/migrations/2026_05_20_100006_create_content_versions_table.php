<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable content versions (draft history, carousel slides JSON).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('content_item_id')
                ->constrained('content_items')
                ->cascadeOnDelete();

            $table->unsignedInteger('version_number');

            $table->text('body')->nullable();

            // Carousel slides: [{ index, headline, body, speaker_notes }]
            $table->jsonb('slides')->nullable();

            $table->jsonb('metadata')->default('{}');

            // user | agent | system
            $table->string('author_type', 32)->default('user');

            $table->uuid('author_id')->nullable();

            $table->boolean('is_current')->default(false);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['content_item_id', 'version_number']);
            $table->index(['workspace_id', 'content_item_id', 'is_current']);
            $table->index(['content_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_versions');
    }
};

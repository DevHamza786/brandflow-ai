<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brand memory RAG chunks — hybrid retrieval (vector + full-text).
 *
 * pgvector: run database/migrations/pgvector/2026_05_20_100004_add_embedding_vector_to_memory_chunks.php
 * after CREATE EXTENSION vector.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memory_chunks', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('brand_profile_id')
                ->nullable()
                ->constrained('brand_profiles')
                ->nullOnDelete();

            // voice | facts | stories | offers | anti_patterns | performance
            $table->string('type', 64);

            $table->text('content');

            // External vector index reference (e.g. Redis HNSW) when not using pgvector.
            $table->string('embedding_id')->nullable();
            $table->string('embedding_model')->nullable();
            $table->unsignedSmallInteger('embedding_dimensions')->nullable();

            // active | superseded | archived
            $table->string('status', 32)->default('active');

            $table->foreignUuid('superseded_by_chunk_id')
                ->nullable()
                ->constrained('memory_chunks')
                ->nullOnDelete();

            $table->string('source')->nullable();
            $table->string('source_uri')->nullable();

            $table->unsignedInteger('chunk_index')->nullable();
            $table->unsignedInteger('token_count')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->jsonb('metadata')->default('{}');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['workspace_id', 'type', 'status']);
            $table->index(['workspace_id', 'status', 'updated_at']);
            $table->index(['brand_profile_id', 'type']);
            $table->index('embedding_id');
        });

        // Sparse (keyword) retrieval — PostgreSQL full-text search.
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE INDEX idx_memory_chunks_content_fts ON memory_chunks USING gin (to_tsvector(\'english\', content))'
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_memory_chunks_content_fts');
        }

        Schema::dropIfExists('memory_chunks');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Optional pgvector column for dense retrieval on memory_chunks.
 *
 * Prerequisites:
 *   CREATE EXTENSION IF NOT EXISTS vector;
 *
 * Configure dimensions via PBOS_EMBEDDING_DIMENSIONS (default 1536).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        $dimensions = (int) env('PBOS_EMBEDDING_DIMENSIONS', 1536);

        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        DB::statement(sprintf(
            'ALTER TABLE memory_chunks ADD COLUMN IF NOT EXISTS embedding vector(%d)',
            $dimensions
        ));

        // HNSW index for approximate nearest-neighbor search (tune m/ef_construction in production).
        DB::statement(
            'CREATE INDEX IF NOT EXISTS idx_memory_chunks_embedding_hnsw ON memory_chunks USING hnsw (embedding vector_cosine_ops)'
        );
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS idx_memory_chunks_embedding_hnsw');
        DB::statement('ALTER TABLE memory_chunks DROP COLUMN IF EXISTS embedding');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canonical brand voice, pillars, and constraints per workspace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('name')->default('Default');

            // Voice, pillars, and guardrails consumed by AI / agents.
            $table->jsonb('voice')->default('{}');
            $table->jsonb('pillars')->default('[]');
            $table->jsonb('constraints')->default('{}');

            // Incremented on bulk memory ingest; agents pin this at run start.
            $table->unsignedBigInteger('memory_version')->default(1);

            $table->boolean('is_primary')->default(true);

            $table->jsonb('metadata')->default('{}');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'is_primary']);
            $table->index(['workspace_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_profiles');
    }
};

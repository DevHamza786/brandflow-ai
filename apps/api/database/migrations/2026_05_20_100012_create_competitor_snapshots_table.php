<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Point-in-time competitor scrape payloads for diffing and alerts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('competitor_id')
                ->constrained('competitors')
                ->cascadeOnDelete();

            $table->timestamp('captured_at');

            // Normalized scrape payload (posts, headline, about, etc.).
            $table->jsonb('payload')->default('{}');

            // SHA-256 of canonical payload for idempotent skip-on-unchanged.
            $table->string('content_hash', 64)->nullable();

            $table->jsonb('metadata')->default('{}');

            $table->timestamps();

            $table->index(['competitor_id', 'captured_at']);
            $table->index(['workspace_id', 'captured_at']);
            $table->index(['competitor_id', 'content_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_snapshots');
    }
};

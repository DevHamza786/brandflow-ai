<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracked competitor LinkedIn profiles per workspace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitors', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->string('linkedin_url', 512);
            $table->string('name')->nullable();
            $table->string('linkedin_urn')->nullable();

            $table->jsonb('labels')->default('[]');
            $table->jsonb('metadata')->default('{}');

            $table->unsignedSmallInteger('scrape_cadence_hours')->default(24);
            $table->timestamp('last_scraped_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['workspace_id', 'linkedin_url']);
            $table->index(['workspace_id', 'is_active', 'last_scraped_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitors');
    }
};

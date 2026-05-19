<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content units: posts, carousels, comments (studio aggregate root).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            // post | carousel | comment
            $table->string('type', 32);

            // draft | approved | scheduled | published | archived
            $table->string('status', 32)->default('draft');

            $table->string('title')->nullable();

            $table->jsonb('metadata')->default('{}');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Tenant list + status boards
            $table->index(['workspace_id', 'status', 'updated_at']);
            $table->index(['workspace_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};

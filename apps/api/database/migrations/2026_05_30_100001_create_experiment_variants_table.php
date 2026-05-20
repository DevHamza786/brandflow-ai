<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Experiment arms (control + challengers) with traffic weights.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiment_variants', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignUuid('experiment_id')
                ->constrained('experiments')
                ->cascadeOnDelete();

            $table->string('variant_key', 64);
            $table->string('label')->nullable();
            $table->boolean('is_control')->default(false);

            $table->decimal('traffic_weight', 5, 4)->default(0.5);

            $table->jsonb('payload')->default('{}');
            $table->jsonb('metadata')->default('{}');

            $table->unsignedInteger('assignment_count')->default(0);

            $table->timestamps();

            $table->unique(['experiment_id', 'variant_key']);
            $table->index(['workspace_id', 'experiment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiment_variants');
    }
};

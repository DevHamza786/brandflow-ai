<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Time-series engagement metrics for posts, profile, and competitors.
 *
 * Feeds analytics rollups and AnalyticsAgent insights.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_metrics', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            // Polymorphic subject without DB-level morph (UUID + type string).
            $table->string('measurable_type', 64);
            $table->uuid('measurable_id');

            $table->date('metric_date');

            // impressions | reactions | comments | shares | engagement_rate | follower_delta | ...
            $table->string('metric_type', 64);

            $table->decimal('value', 20, 4);

            $table->jsonb('dimensions')->nullable();

            // linkedin_scrape | manual | import
            $table->string('source', 32)->default('linkedin_scrape');

            $table->timestamp('captured_at')->useCurrent();

            $table->jsonb('metadata')->default('{}');

            $table->timestamps();

            $table->unique([
                'workspace_id',
                'measurable_type',
                'measurable_id',
                'metric_date',
                'metric_type',
            ], 'engagement_metrics_unique_daily');

            $table->index(['workspace_id', 'metric_date']);
            $table->index(['workspace_id', 'measurable_type', 'measurable_id', 'metric_date']);
            $table->index(['measurable_type', 'measurable_id', 'metric_type', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_metrics');
    }
};

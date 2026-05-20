<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured competitor intelligence on snapshots (query/index friendly; ML-ready).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitors', function (Blueprint $table): void {
            $table->timestampTz('last_analyzed_at')->nullable()->after('last_scraped_at');
            $table->decimal('intelligence_score', 8, 4)->nullable()->after('last_analyzed_at');
            $table->index(['workspace_id', 'intelligence_score']);
        });

        Schema::table('competitor_snapshots', function (Blueprint $table): void {
            $table->unsignedInteger('posts_count')->default(0)->after('content_hash');
            $table->decimal('avg_engagement_rate', 14, 8)->nullable()->after('posts_count');
            $table->decimal('posts_per_week', 10, 4)->nullable()->after('avg_engagement_rate');
            $table->decimal('intelligence_score', 8, 4)->nullable()->after('posts_per_week');

            $table->jsonb('engagement_metrics')->default('{}')->after('intelligence_score');
            $table->jsonb('hook_patterns')->default('{}')->after('engagement_metrics');
            $table->jsonb('posting_cadence')->default('{}')->after('hook_patterns');
            $table->jsonb('content_structure')->default('{}')->after('posting_cadence');
            $table->jsonb('cta_patterns')->default('{}')->after('content_structure');
            $table->jsonb('trend_summary')->default('{}')->after('cta_patterns');
            $table->jsonb('ml_features')->default('{}')->after('trend_summary');

            $table->index(['workspace_id', 'intelligence_score']);
            $table->index(['workspace_id', 'posts_count']);
        });
    }

    public function down(): void
    {
        Schema::table('competitor_snapshots', function (Blueprint $table): void {
            $table->dropIndex(['workspace_id', 'intelligence_score']);
            $table->dropIndex(['workspace_id', 'posts_count']);
            $table->dropColumn([
                'posts_count',
                'avg_engagement_rate',
                'posts_per_week',
                'intelligence_score',
                'engagement_metrics',
                'hook_patterns',
                'posting_cadence',
                'content_structure',
                'cta_patterns',
                'trend_summary',
                'ml_features',
            ]);
        });

        Schema::table('competitors', function (Blueprint $table): void {
            $table->dropIndex(['workspace_id', 'intelligence_score']);
            $table->dropColumn(['last_analyzed_at', 'intelligence_score']);
        });
    }
};

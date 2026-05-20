<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personalization fields for brand memory — complements legacy voice/pillars/constraints.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_profiles', function (Blueprint $table): void {
            $table->text('brand_voice')->nullable()->after('constraints');
            $table->jsonb('tone_profile')->default('{}')->after('brand_voice');
            $table->jsonb('target_audience')->default('{}')->after('tone_profile');
            $table->jsonb('banned_phrases')->default('[]')->after('target_audience');
            $table->jsonb('preferred_ctas')->default('[]')->after('banned_phrases');
            $table->jsonb('preferred_hook_patterns')->default('[]')->after('preferred_ctas');
            $table->jsonb('style_guidelines')->default('{}')->after('preferred_hook_patterns');
        });

        Schema::table('brand_profiles', function (Blueprint $table): void {
            $table->index(['workspace_id', 'memory_version']);
        });
    }

    public function down(): void
    {
        Schema::table('brand_profiles', function (Blueprint $table): void {
            $table->dropIndex(['workspace_id', 'memory_version']);
        });

        Schema::table('brand_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'brand_voice',
                'tone_profile',
                'target_audience',
                'banned_phrases',
                'preferred_ctas',
                'preferred_hook_patterns',
                'style_guidelines',
            ]);
        });
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('scheduled_posts', 'platform')) {
                $table->string('platform', 32)->default('linkedin')->after('workspace_id');
            }
            if (! Schema::hasColumn('scheduled_posts', 'schedule_pattern')) {
                $table->string('schedule_pattern', 32)->default('once')->after('status');
            }
            if (! Schema::hasColumn('scheduled_posts', 'recurrence_rule')) {
                $table->json('recurrence_rule')->nullable()->after('schedule_pattern');
            }
            if (! Schema::hasColumn('scheduled_posts', 'series_id')) {
                $table->uuid('series_id')->nullable()->after('recurrence_rule');
            }
            if (! Schema::hasColumn('scheduled_posts', 'workflow_run_id')) {
                $table->foreignUuid('workflow_run_id')
                    ->nullable()
                    ->after('metadata')
                    ->constrained('workflow_runs')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('scheduled_posts', 'execution_id')) {
                $table->uuid('execution_id')->nullable()->unique()->after('workflow_run_id');
            }
            if (! Schema::hasColumn('scheduled_posts', 'last_dispatched_at')) {
                $table->timestampTz('last_dispatched_at')->nullable()->after('last_attempt_at');
            }
            if (! Schema::hasColumn('scheduled_posts', 'orchestration_metadata')) {
                $table->json('orchestration_metadata')->nullable()->after('metadata');
            }
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table): void {
            if (Schema::hasColumn('scheduled_posts', 'workflow_run_id')) {
                try {
                    $table->dropForeign(['workflow_run_id']);
                } catch (\Throwable) {
                    // ignore FK naming differences across drivers
                }
            }
        });

        Schema::table('scheduled_posts', function (Blueprint $table): void {
            foreach ([
                'platform',
                'schedule_pattern',
                'recurrence_rule',
                'series_id',
                'workflow_run_id',
                'execution_id',
                'last_dispatched_at',
                'orchestration_metadata',
            ] as $col) {
                if (Schema::hasColumn('scheduled_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

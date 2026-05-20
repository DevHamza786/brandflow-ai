<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One generated output per workflow execution (hook_generation, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generated_outputs', function (Blueprint $table): void {
            $table->unique(
                ['workspace_id', 'workflow_run_id', 'type'],
                'generated_outputs_ws_workflow_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('generated_outputs', function (Blueprint $table): void {
            $table->dropUnique('generated_outputs_ws_workflow_type_unique');
        });
    }
};

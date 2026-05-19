<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant root: every workspace-scoped row references workspaces.id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('slug')->unique();

            // Plan reference (Stripe / billing integration added later).
            $table->string('plan_id')->nullable()->index();

            // Tenant settings: feature flags, publish caps, timezone default, etc.
            $table->jsonb('settings')->default('{}');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};

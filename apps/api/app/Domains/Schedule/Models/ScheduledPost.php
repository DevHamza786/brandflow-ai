<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Models;

use App\Domains\Content\Models\ContentVersion;
use App\Domains\Integrations\Models\LinkedInIntegration;
use App\Domains\AI\Models\GeneratedOutput;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduledPost extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'scheduled_posts';

    protected $fillable = [
        'id',
        'workspace_id',
        'platform',
        'schedule_pattern',
        'recurrence_rule',
        'series_id',
        'workflow_run_id',
        'execution_id',
        'last_dispatched_at',
        'orchestration_metadata',
        'content_item_id',
        'content_version_id',
        'linkedin_integration_id',
        'generated_output_id',
        'content',
        'publish_at',
        'scheduled_for',
        'timezone',
        'status',
        'linkedin_account_id',
        'linkedin_urn',
        'provider_post_id',
        'published_at',
        'last_attempt_at',
        'attempt_count',
        'error',
        'error_details',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
            'scheduled_for' => 'datetime',
            'published_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'last_dispatched_at' => 'datetime',
            'recurrence_rule' => 'array',
            'orchestration_metadata' => 'array',
            'error' => 'array',
            'error_details' => 'array',
            'metadata' => 'array',
            'attempt_count' => 'integer',
        ];
    }

    public function linkedinIntegration(): BelongsTo
    {
        return $this->belongsTo(LinkedInIntegration::class, 'linkedin_integration_id');
    }

    public function generatedOutput(): BelongsTo
    {
        return $this->belongsTo(GeneratedOutput::class, 'generated_output_id');
    }

    public function contentVersion(): BelongsTo
    {
        return $this->belongsTo(ContentVersion::class, 'content_version_id');
    }
}

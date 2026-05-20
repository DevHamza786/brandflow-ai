<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Workspace OAuth integration — tokens encrypted at rest via Laravel encrypted cast.
 */
class LinkedInIntegration extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'linkedin_integrations';

    protected $fillable = [
        'workspace_id',
        'provider',
        'linkedin_member_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'metadata',
        'status',
        'connected_at',
        'last_synced_at',
        'last_error',
        'refresh_attempts',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'scopes' => 'array',
            'metadata' => 'array',
            'refresh_attempts' => 'integer',
        ];
    }
}

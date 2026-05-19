<?php

declare(strict_types=1);

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContentVersion extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'content_item_id',
        'version_number',
        'body',
        'slides',
        'metadata',
        'author_type',
        'author_id',
        'is_current',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'slides' => 'array',
            'metadata' => 'array',
            'is_current' => 'boolean',
        ];
    }

    public function extractOpeningLines(int $lineCount = 2): string
    {
        if ($this->body === null || trim($this->body) === '') {
            return '';
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($this->body)) ?: [];

        return trim(implode("\n", array_slice($lines, 0, $lineCount)));
    }
}

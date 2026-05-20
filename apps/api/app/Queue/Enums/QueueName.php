<?php

declare(strict_types=1);

namespace App\Queue\Enums;

/**
 * PBOS Redis queue names (priority order matches Horizon supervisors).
 */
enum QueueName: string
{
    case Critical = 'critical';

    /** Batch cron scheduler — claims due rows and pushes publish jobs ahead of scheduling workers. */
    case Orchestration = 'orchestration';

    case Scheduling = 'scheduling';
    case Workflows = 'workflows';
    case Ai = 'ai';
    case Scraping = 'scraping';
    case Analytics = 'analytics';
    case Default = 'default';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

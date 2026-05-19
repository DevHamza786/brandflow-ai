<?php

declare(strict_types=1);

namespace App\Domains\Shared\Jobs;

use App\Queue\Jobs\AbstractQueueJob;

/**
 * @deprecated Extend App\Queue\Jobs\AbstractQueueJob directly in new code.
 */
abstract class BaseQueueJob extends AbstractQueueJob
{
}

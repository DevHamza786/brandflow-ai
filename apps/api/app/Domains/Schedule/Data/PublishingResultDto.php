<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Data;

use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

final class PublishingResultDto extends DataTransferObject
{
    /**
     * Provider-native post URN / id (e.g. LinkedIn share URN).
     *
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public readonly ?string $providerPostId,
        public readonly array $rawResponse = [],
        public readonly ?CarbonInterface $publishedAt = null,
    ) {
    }
}

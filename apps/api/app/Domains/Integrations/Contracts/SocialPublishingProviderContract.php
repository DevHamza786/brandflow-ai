<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Contracts;

use App\Domains\Schedule\Data\PublishingResultDto;

/**
 * Multi-platform publishing entry point (LinkedIn implementation first).
 */
interface SocialPublishingProviderContract
{
    /**
     * Human-readable provider id (linkedin, x, etc.).
     */
    public function slug(): string;

    /**
     * Publish a simple text post (LinkedIn UGC / future: tweet body).
     *
     * @param  array<string, mixed>  $context  e.g. linkedin_member_id, metadata passthrough
     */
    public function publishTextPost(string $accessToken, string $text, array $context = []): PublishingResultDto;
}

<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Services;

use App\Domains\AI\Contracts\GeneratedOutputRepositoryContract;
use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use App\Domains\Integrations\Contracts\SocialPublishingProviderContract;
use App\Domains\Integrations\Exceptions\UnretryablePublishingException;
use App\Domains\Integrations\Services\LinkedInTokenRefreshService;
use App\Domains\Integrations\Support\IntegrationLogger;
use App\Domains\Schedule\Data\PublishingResultDto;
use App\Domains\Schedule\Data\ScheduledPostDto;
use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Schedule\Events\ScheduledPostPublishFailed;
use App\Domains\Schedule\Events\ScheduledPostPublished;
use App\Domains\Schedule\Events\ScheduledPostPublishingStarted;
use App\Domains\Schedule\Repositories\ScheduledPostRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

/**
 * Queue-safe orchestration for LinkedIn publish attempts (provider behind SocialPublishingProviderContract).
 *
 * Scheduled posts use the concrete {@see ScheduledPostRepository} so Horizon/long-lived workers always
 * auto-resolve the dependency without relying on interface→implementation bindings from container cache.
 */
final class LinkedInPublishingService
{
    private const TEXT_KEYS = [
        'text', 'body', 'hook', 'content', 'message', 'headline', 'post',
    ];

    public function __construct(
        private readonly ScheduledPostRepository $scheduledPosts,
        private readonly LinkedInIntegrationRepositoryContract $integrations,
        private readonly GeneratedOutputRepositoryContract $generatedOutputs,
        private readonly LinkedInTokenRefreshService $tokenRefresh,
        private readonly SocialPublishingProviderContract $linkedInPublisher,
        private readonly IntegrationLogger $logger,
    ) {
    }

    public function publishScheduledPost(string $workspaceId, string $scheduledPostId, ?string $traceId = null): void
    {
        $traceId ??= $this->logger->traceId();

        $lock = Cache::lock('scheduled_post.publish:'.$workspaceId.':'.$scheduledPostId, 120);
        if (! $lock->get()) {
            $this->logger->info('publishing.lock_contended', [
                'trace_id' => $traceId,
                'workspace_id' => $workspaceId,
                'scheduled_post_id' => $scheduledPostId,
            ]);

            throw new \RuntimeException('Another publish attempt holds the lock; retrying.');
        }

        try {
            $this->runPublish($workspaceId, $scheduledPostId, $traceId);
        } finally {
            $lock->release();
        }
    }

    private function runPublish(string $workspaceId, string $scheduledPostId, string $traceId): void
    {
        $post = $this->scheduledPosts->findById($workspaceId, $scheduledPostId);

        if ($post === null) {
            throw new UnretryablePublishingException('Scheduled post not found.', [
                'workspace_id' => $workspaceId,
                'scheduled_post_id' => $scheduledPostId,
            ]);
        }

        if ($post->status === ScheduledPostStatus::Published && $post->providerPostId !== null && $post->providerPostId !== '') {
            $this->logger->info('publishing.idempotent_skip', [
                'trace_id' => $traceId,
                'workspace_id' => $workspaceId,
                'scheduled_post_id' => $scheduledPostId,
                'provider_post_id' => $post->providerPostId,
            ]);

            return;
        }

        if ($post->status === ScheduledPostStatus::Cancelled) {
            return;
        }

        if ($post->linkedinIntegrationId === null || $post->linkedinIntegrationId === '') {
            throw new UnretryablePublishingException('Scheduled post missing LinkedIn integration.', [
                'scheduled_post_id' => $scheduledPostId,
            ]);
        }

        if ($post->scheduledFor !== null && $post->scheduledFor->isFuture()) {
            $this->logger->info('publishing.not_due_yet', [
                'trace_id' => $traceId,
                'scheduled_for' => $post->scheduledFor->toIso8601String(),
            ]);

            return;
        }

        try {
            $body = $this->resolvePostBody($workspaceId, $post);

            $integrationDto = $this->integrations->findById($workspaceId, $post->linkedinIntegrationId);
            if ($integrationDto === null) {
                throw new UnretryablePublishingException('LinkedIn integration not found.');
            }

            if (! $integrationDto->status->isActive()) {
                throw new UnretryablePublishingException('LinkedIn integration is not connected.', [
                    'status' => $integrationDto->status->value,
                ]);
            }

            Event::dispatch(new ScheduledPostPublishingStarted($post, $traceId));

            $this->scheduledPosts->markPublishing($workspaceId, $scheduledPostId);

            $integrationDto = $this->tokenRefresh->refreshIfExpiring($integrationDto, $traceId);
            $accessToken = $this->integrations->getDecryptedAccessToken($workspaceId, $post->linkedinIntegrationId);

            if ($accessToken === null || $accessToken === '') {
                throw new UnretryablePublishingException('No LinkedIn access token available.');
            }

            $memberId = $integrationDto->linkedinMemberId ?? '';
            $result = $this->linkedInPublisher->publishTextPost(
                $accessToken,
                $body,
                [
                    'linkedin_member_id' => $memberId,
                    'workspace_id' => $workspaceId,
                    'scheduled_post_id' => $scheduledPostId,
                ],
            );

            $this->persistSuccess($workspaceId, $scheduledPostId, $post, $result, $traceId);
        } catch (UnretryablePublishingException $e) {
            $this->persistFailure($workspaceId, $scheduledPostId, $post, $e, $traceId);
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error('publishing.attempt_failed', [
                'trace_id' => $traceId,
                'workspace_id' => $workspaceId,
                'scheduled_post_id' => $scheduledPostId,
            ], $e);
            throw $e;
        }
    }

    private function persistSuccess(
        string $workspaceId,
        string $scheduledPostId,
        ScheduledPostDto $post,
        PublishingResultDto $result,
        string $traceId,
    ): void {
        $metadataPatch = [
            'linkedin_publish' => [
                'provider' => 'linkedin',
                'raw_response' => $result->rawResponse,
                'published_at' => $result->publishedAt?->toIso8601String(),
            ],
            'last_trace_id' => $traceId,
        ];

        $updated = $this->scheduledPosts->markPublished(
            $workspaceId,
            $scheduledPostId,
            $result->providerPostId,
            $result->providerPostId,
            $metadataPatch,
        );

        Event::dispatch(new ScheduledPostPublished($updated, $traceId));

        $this->logger->info('publishing.success', [
            'trace_id' => $traceId,
            'workspace_id' => $workspaceId,
            'scheduled_post_id' => $scheduledPostId,
            'provider_post_id' => $result->providerPostId,
        ]);
    }

    private function persistFailure(
        string $workspaceId,
        string $scheduledPostId,
        ScheduledPostDto $post,
        UnretryablePublishingException $e,
        string $traceId,
    ): void {
        $details = array_merge($e->context, [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'trace_id' => $traceId,
        ]);

        $failed = $this->scheduledPosts->markFailed($workspaceId, $scheduledPostId, $details);

        Event::dispatch(new ScheduledPostPublishFailed($failed, $traceId, $details));

        $this->logger->error('publishing.failed', [
            'trace_id' => $traceId,
            'workspace_id' => $workspaceId,
            'scheduled_post_id' => $scheduledPostId,
        ], $e);
    }

    private function resolvePostBody(string $workspaceId, ScheduledPostDto $post): string
    {
        $direct = $post->content !== null ? trim($post->content) : '';
        if ($direct !== '') {
            return $direct;
        }

        if ($post->generatedOutputId !== null && $post->generatedOutputId !== '') {
            $output = $this->generatedOutputs->findById($workspaceId, $post->generatedOutputId);
            if ($output === null) {
                throw new UnretryablePublishingException('Generated output not found for scheduled post.');
            }

            $extracted = $this->extractTextFromGeneratedOutput($output->output?->payload ?? []);
            if ($extracted !== '') {
                return $extracted;
            }
        }

        throw new UnretryablePublishingException('No publishable text: set content or a generated output with extractable text.', [
            'scheduled_post_id' => $post->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractTextFromGeneratedOutput(array $payload): string
    {
        foreach (self::TEXT_KEYS as $key) {
            if (isset($payload[$key]) && is_string($payload[$key]) && trim($payload[$key]) !== '') {
                return trim($payload[$key]);
            }
        }

        if (isset($payload['variants']) && is_array($payload['variants'])) {
            foreach ($payload['variants'] as $variant) {
                if (! is_array($variant)) {
                    continue;
                }
                foreach (['text', 'hook', 'body'] as $k) {
                    if (isset($variant[$k]) && is_string($variant[$k]) && trim($variant[$k]) !== '') {
                        return trim($variant[$k]);
                    }
                }
            }
        }

        return '';
    }
}

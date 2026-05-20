<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Contracts\SocialPublishingProviderContract;
use App\Domains\Integrations\Exceptions\UnretryablePublishingException;
use App\Domains\Schedule\Data\PublishingResultDto;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * LinkedIn UGC text posts — HTTP isolated here so other platforms can implement the same contract.
 */
final class LinkedInSocialPublisher implements SocialPublishingProviderContract
{
    public function slug(): string
    {
        return 'linkedin';
    }

    /**
     * @param  array<string, mixed>  $context  expects linkedin_member_id
     */
    public function publishTextPost(string $accessToken, string $text, array $context = []): PublishingResultDto
    {
        $memberId = (string) ($context['linkedin_member_id'] ?? '');
        if ($memberId === '' || trim($text) === '') {
            throw new UnretryablePublishingException('Missing LinkedIn member id or empty post body.', [
                'has_member_id' => $memberId !== '',
                'text_length' => strlen($text),
            ]);
        }

        $baseUrl = rtrim((string) config('integrations.linkedin_publishing.api_base', 'https://api.linkedin.com'), '/');
        $path = (string) config('integrations.linkedin_publishing.ugc_path', '/v2/ugcPosts');
        $url = $baseUrl.$path;
        $version = (string) config('integrations.linkedin_publishing.api_version', '202404');
        $timeout = (int) config('integrations.linkedin.http_timeout_seconds', 30);

        $payload = [
            'author' => 'urn:li:person:'.$memberId,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => [
                        'text' => $text,
                    ],
                    'shareMediaCategory' => 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'LinkedIn-Version' => $version,
                ])
                ->timeout($timeout)
                ->acceptJson()
                ->post($url, $payload)
                ->throw();
        } catch (RequestException $e) {
            $this->failHttp($e);
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new UnretryablePublishingException('Invalid LinkedIn API response shape.', [
                'status' => $response->status(),
            ]);
        }

        $postId = isset($json['id']) ? (string) $json['id'] : null;

        return new PublishingResultDto(
            providerPostId: $postId,
            rawResponse: $json,
            publishedAt: now(),
        );
    }

    private function failHttp(RequestException $e): never
    {
        $response = $e->response;
        $status = $response !== null ? $response->status() : 0;
        $body = $response !== null ? $response->json() : null;
        $payload = is_array($body) ? $body : ['raw' => $response?->body()];

        if ($status >= 400 && $status < 500 && $status !== 429) {
            throw new UnretryablePublishingException(
                'LinkedIn refused the publish request.',
                [
                    'http_status' => $status,
                    'body' => $payload,
                ],
                $status,
                $e,
            );
        }

        throw $e;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Integrations;

use App\Domains\Integrations\Actions\CompleteLinkedInOAuthAction;
use App\Domains\Integrations\Exceptions\LinkedInOAuthException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * OAuth callback — web route (LinkedIn redirects browser here, no X-Workspace-Id header).
 */
final class LinkedInOAuthCallbackController extends Controller
{
    public function __construct(
        private readonly CompleteLinkedInOAuthAction $completeOAuth,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $error = $request->query('error');
        if (is_string($error) && $error !== '') {
            return $this->redirectError(
                description: (string) $request->query('error_description', $error),
            );
        }

        $code = $request->query('code');
        $state = $request->query('state');

        if (! is_string($code) || $code === '' || ! is_string($state) || $state === '') {
            return $this->redirectError('Missing authorization code or state.');
        }

        try {
            $result = $this->completeOAuth->execute($state, $code);
        } catch (LinkedInOAuthException $e) {
            return $this->redirectError($e->getMessage());
        } catch (\Throwable $e) {
            return $this->redirectError('LinkedIn connection failed. Please try again.');
        }

        $url = $result['redirect_url'];
        $separator = str_contains($url, '?') ? '&' : '?';

        return redirect()->away($url.$separator.'integration_id='.$result['integration']->id);
    }

    private function redirectError(string $description): RedirectResponse
    {
        $base = (string) config('integrations.default_error_redirect');
        $separator = str_contains($base, '?') ? '&' : '?';

        return redirect()->away($base.$separator.'message='.urlencode($description));
    }
}

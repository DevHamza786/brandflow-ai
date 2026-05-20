<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Support;

use App\Domains\Integrations\Models\LinkedInIntegration;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Documents and validates encrypted credential access — persistence uses model encrypted cast.
 */
final class IntegrationCredentialVault
{
    public function assertEncryptionConfigured(): void
    {
        if (config('app.key') === null || config('app.key') === '') {
            throw new \RuntimeException('APP_KEY must be set for integration credential encryption.');
        }
    }

    /**
     * Verify ciphertext is not stored as plaintext (heuristic for audits).
     */
    public function looksEncrypted(?string $stored): bool
    {
        if ($stored === null || $stored === '') {
            return true;
        }

        try {
            decrypt($stored, unserialize: false);

            return true;
        } catch (DecryptException) {
            return ! str_starts_with($stored, 'AQ'); // not a raw bearer token prefix heuristic
        }
    }

    public function redactForLogs(LinkedInIntegration $model): array
    {
        return [
            'id' => $model->id,
            'workspace_id' => $model->workspace_id,
            'has_access_token' => $model->access_token !== null && $model->access_token !== '',
            'has_refresh_token' => $model->refresh_token !== null && $model->refresh_token !== '',
            'access_token_encrypted' => $this->looksEncrypted(
                $model->getRawOriginal('access_token'),
            ),
        ];
    }
}

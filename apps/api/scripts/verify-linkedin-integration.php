<?php

declare(strict_types=1);

/**
 * Verifies linkedin_integrations schema, encryption, workspace scoping, and OAuth services.
 *
 * Usage: php scripts/verify-linkedin-integration.php [workspace-uuid]
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use App\Domains\Integrations\Data\CreateLinkedInIntegrationDto;
use App\Domains\Integrations\Models\LinkedInIntegration;
use App\Domains\Integrations\Services\LinkedInOAuthService;
use App\Domains\Integrations\Services\OAuthStateStore;
use App\Domains\Integrations\Support\IntegrationCredentialVault;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

$workspaceId = $argv[1] ?? '9bb59c64-347a-48b3-b010-e41b5cdc6f4d';

echo "=== LinkedIn Integration Verification ===\n\n";

if (! Schema::hasTable('linkedin_integrations')) {
    fwrite(STDERR, "FAIL: linkedin_integrations table missing — run migrations.\n");
    exit(1);
}
echo "PASS: linkedin_integrations table exists\n";

$columns = Schema::getColumnListing('linkedin_integrations');
$required = [
    'id', 'workspace_id', 'provider', 'linkedin_member_id',
    'access_token', 'refresh_token', 'token_expires_at', 'scopes', 'metadata',
    'status', 'connected_at', 'last_synced_at',
];
foreach ($required as $col) {
    if (! in_array($col, $columns, true)) {
        fwrite(STDERR, "FAIL: missing column {$col}\n");
        exit(1);
    }
}
echo 'PASS: required columns present ('.count($columns)." total)\n";

$indexes = collect(DB::select("
    SELECT indexname FROM pg_indexes
    WHERE tablename = 'linkedin_integrations'
"))->pluck('indexname')->all();
echo 'INFO: indexes: '.implode(', ', $indexes)."\n";

$vault = app(IntegrationCredentialVault::class);
$vault->assertEncryptionConfigured();
echo "PASS: APP_KEY configured for encryption\n";

$repo = app(LinkedInIntegrationRepositoryContract::class);
$plainToken = 'test_access_'.Str::random(32);
$plainRefresh = 'test_refresh_'.Str::random(32);

$integration = $repo->create(new CreateLinkedInIntegrationDto(
    workspaceId: $workspaceId,
    accessToken: $plainToken,
    refreshToken: $plainRefresh,
    linkedinMemberId: 'verify_'.Str::random(8),
    scopes: ['openid', 'profile'],
    metadata: ['verify' => true],
));

$raw = DB::table('linkedin_integrations')->where('id', $integration->id)->first();
if ($raw === null) {
    fwrite(STDERR, "FAIL: row not found\n");
    exit(1);
}

if ($raw->access_token === $plainToken || str_contains((string) $raw->access_token, $plainToken)) {
    fwrite(STDERR, "FAIL: access_token stored in plaintext\n");
    exit(1);
}
if ($raw->refresh_token === $plainRefresh) {
    fwrite(STDERR, "FAIL: refresh_token stored in plaintext\n");
    exit(1);
}
echo "PASS: tokens NOT stored as plaintext in DB\n";

$decrypted = $repo->getDecryptedAccessToken($workspaceId, $integration->id);
if ($decrypted !== $plainToken) {
    fwrite(STDERR, "FAIL: decrypted access token mismatch\n");
    exit(1);
}
echo "PASS: decrypt round-trip for access token\n";

$otherWorkspace = (string) Str::uuid();
$leaked = $repo->findById($otherWorkspace, $integration->id);
if ($leaked !== null) {
    fwrite(STDERR, "FAIL: cross-workspace read allowed\n");
    exit(1);
}
echo "PASS: workspace scoping blocks cross-tenant access\n";

$oauth = app(LinkedInOAuthService::class);
$stateStore = app(OAuthStateStore::class);

try {
    $begin = $oauth->beginConnect($workspaceId);
    if (! isset($begin['authorization_url'], $begin['state'])) {
        throw new RuntimeException('beginConnect missing fields');
    }
    echo "PASS: OAuth connect URL generated\n";
    echo "  URL prefix: ".substr($begin['authorization_url'], 0, 60)."...\n";

    $decoded = $stateStore->decodeStateParameter($begin['state']);
    if ($decoded['workspace_id'] !== $workspaceId) {
        throw new RuntimeException('state workspace mismatch');
    }
    echo "PASS: OAuth state encodes workspace_id\n";
} catch (Throwable $e) {
    if (str_contains($e->getMessage(), 'LINKEDIN_CLIENT_ID')) {
        echo "SKIP: OAuth URL (LINKEDIN_CLIENT_ID not set in .env)\n";
    } else {
        throw $e;
    }
}

LinkedInIntegration::query()->where('id', $integration->id)->forceDelete();
echo "\nCleanup: removed verify integration row\n";

echo "\nAll linkedin integration checks passed.\n";

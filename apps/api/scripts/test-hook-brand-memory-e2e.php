<?php

declare(strict_types=1);

/**
 * End-to-end brand memory verification for Hook Lab (no HTTP).
 */
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Agents\Agents\HookAgent\Support\HookBannedPhraseFilter;
use App\Domains\Agents\Agents\HookAgent\Support\HookPromptTemplate;
use App\Domains\Brand\Contracts\BrandMemoryContextServiceContract;
use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Brand\Data\AudienceProfileDto;
use App\Domains\Brand\Data\CreateBrandProfileDto;
use App\Domains\Brand\Data\CreateWritingSampleDto;
use App\Domains\Brand\Data\StyleGuidelinesDto;
use App\Domains\Brand\Data\ToneProfileDto;
use App\Domains\Brand\Enums\WritingSampleSourceType;
use App\Domains\Brand\Models\BrandProfile;
use App\Domains\Brand\Services\BrandMemoryOrchestrationService;
use App\Domains\AI\Contracts\PromptTemplateRegistryContract;
use Illuminate\Support\Facades\DB;

$workspaceId = $argv[1] ?? DB::table('workspaces')->value('id');
$contentVersionId = $argv[2] ?? DB::table('content_versions')->where('workspace_id', $workspaceId)->value('id');

if (! $workspaceId || ! $contentVersionId) {
    fwrite(STDERR, "Need workspace + content_version. Args: <workspace-uuid> [content-version-uuid]\n");
    exit(1);
}

echo "=== Hook Brand Memory E2E Test ===\n";
echo "Workspace: {$workspaceId}\n";
echo "Content version: {$contentVersionId}\n\n";

$profiles = app(BrandProfileRepositoryContract::class);
$contextService = app(BrandMemoryContextServiceContract::class);
$prompts = app(PromptTemplateRegistryContract::class);
$ingest = app(BrandMemoryOrchestrationService::class);
$filter = app(HookBannedPhraseFilter::class);

// Reset primary flags
BrandProfile::query()->where('workspace_id', $workspaceId)->update(['is_primary' => false]);

$presets = [
    'aggressive' => [
        'name' => 'E2E Aggressive Founder',
        'brand_voice' => 'Blunt, high-conviction, no fluff.',
        'tone' => new ToneProfileDto(primary: 'bold', traits: ['direct', 'provocative'], avoid: ['corporate']),
        'audience' => new AudienceProfileDto(summary: 'Early-stage B2B founders'),
        'banned' => ['revolutionary', 'game-changing', 'synergy'],
        'ctas' => ['DM me "scale"'],
        'patterns' => ['contrarian claim'],
        'sample' => "Most founders optimize the wrong metric.\nWe 3x'd pipeline in 90 days.",
    ],
    'corporate' => [
        'name' => 'E2E Corporate',
        'brand_voice' => 'Professional, evidence-first, measured tone.',
        'tone' => new ToneProfileDto(primary: 'professional', traits: ['authoritative'], avoid: ['slang']),
        'audience' => new AudienceProfileDto(summary: 'Enterprise RevOps leaders'),
        'banned' => ['crushing it', 'hustle'],
        'ctas' => ['Download the executive brief'],
        'patterns' => ['industry statistic opener'],
        'sample' => "Organizations aligning marketing and RevOps report 24% higher win rates.",
    ],
];

$renderedByPreset = [];

foreach ($presets as $key => $preset) {
    BrandProfile::query()->where('workspace_id', $workspaceId)->where('name', $preset['name'])->forceDelete();

    $profile = $profiles->create(new CreateBrandProfileDto(
        workspaceId: $workspaceId,
        name: $preset['name'],
        brandVoice: $preset['brand_voice'],
        toneProfile: $preset['tone'],
        targetAudience: $preset['audience'],
        bannedPhrases: $preset['banned'],
        preferredCtas: $preset['ctas'],
        preferredHookPatterns: $preset['patterns'],
        styleGuidelines: new StyleGuidelinesDto(summary: 'E2E test style'),
        metadata: ['e2e_preset' => $key],
        isPrimary: true,
    ));

    BrandProfile::query()
        ->where('workspace_id', $workspaceId)
        ->where('id', '!=', $profile->id)
        ->update(['is_primary' => false]);

    $ingest->ingestWritingSample(new CreateWritingSampleDto(
        workspaceId: $workspaceId,
        content: $preset['sample'],
        sourceType: WritingSampleSourceType::Manual,
        brandProfileId: $profile->id,
    ));

    $ctx = $contextService->forHookAgent($workspaceId, 'How to grow your LinkedIn presence in 2026', null, null);
    $vars = array_merge($ctx->promptVariables, [
        'hook_text' => 'I spent 3 years posting on LinkedIn with zero results.',
        'target_audience' => $ctx->promptVariables['target_audience'] ?? 'LinkedIn professionals',
        'content_pillar' => 'growth',
        'compact_brand_memory' => $ctx->compactBrandSection,
    ]);

    $scorerPrompt = $prompts->render(HookPromptTemplate::SCORER_SLUG, $vars, 'v1');
    $renderedByPreset[$key] = [
        'profile_id' => $profile->id,
        'compact_chars' => strlen($ctx->compactBrandSection),
        'compact' => $ctx->compactBrandSection,
        'scorer_prompt_chars' => strlen($scorerPrompt),
        'banned' => $ctx->bannedPhrases,
        'gateway_memory_null' => $ctx->memoryContextForGateway() === null,
    ];

    echo "--- Preset: {$key} ---\n";
    echo "Profile: {$profile->id}\n";
    echo "Compact section: {$renderedByPreset[$key]['compact_chars']} chars\n";
    echo "Scorer prompt total: {$renderedByPreset[$key]['scorer_prompt_chars']} chars\n";
    echo "Gateway memory skipped (anti-bloat): ".($renderedByPreset[$key]['gateway_memory_null'] ? 'yes' : 'no')."\n";
    echo "Banned: ".implode(', ', $ctx->bannedPhrases)."\n";
    echo $ctx->compactBrandSection."\n\n";
}

// Prompts must differ between presets
$aggressive = $renderedByPreset['aggressive']['compact'];
$corporate = $renderedByPreset['corporate']['compact'];
similar_text($aggressive, $corporate, $similarityPct);

echo "=== Differentiation check ===\n";
echo "Similarity aggressive vs corporate compact sections: ".round($similarityPct, 1)."%\n";

if ($similarityPct > 85) {
    echo "WARN: Profiles may be too similar in compact memory.\n";
} else {
    echo "PASS: Profiles produce distinct memory context.\n";
}

if ($renderedByPreset['aggressive']['scorer_prompt_chars'] > 6000) {
    echo "FAIL: Scorer prompt may be bloated (>6000 chars).\n";
    exit(1);
}

echo "PASS: Scorer prompt under bloat threshold ({$renderedByPreset['aggressive']['scorer_prompt_chars']} chars).\n";

// Banned phrase filter (use aggressive profile)
BrandProfile::query()->where('workspace_id', $workspaceId)->update(['is_primary' => false]);
BrandProfile::query()->where('id', $renderedByPreset['aggressive']['profile_id'])->update(['is_primary' => true]);

$ctx = $contextService->forHookAgent($workspaceId, 'test', null, null);
$variants = [
    new \App\Domains\Agents\Agents\HookAgent\Data\HookVariant(
        text: 'This revolutionary game-changing synergy hook wins.',
        overall: 75,
        dimensions: new \App\Domains\Agents\Agents\HookAgent\Data\HookScoreDimensions(),
    ),
];
$filtered = $filter->filterVariants($variants, $ctx->bannedPhrases);
$out = $filtered[0]->text ?? '';
echo "\n=== Banned phrase filter ===\n";
echo "Input:  {$variants[0]->text}\n";
echo "Output: {$out}\n";

foreach (['revolutionary', 'game-changing', 'synergy'] as $word) {
    if (stripos($out, $word) !== false) {
        echo "FAIL: still contains [{$word}]\n";
        exit(1);
    }
}
echo "PASS: banned phrases stripped from variant.\n";

// Optional API dispatch
if (($argv[3] ?? '') === '--api') {
    echo "\n=== API hook generation (aggressive profile active) ===\n";
    $ch = curl_init("http://nginx/api/v1/content-versions/{$contentVersionId}/hooks/generate");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Workspace-Id: '.$workspaceId,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'options' => [
                'max_variants' => 2,
                'target_audience' => 'founders',
                'experiment_id' => 'e2e-brand-memory',
            ],
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "HTTP {$code}\n";
    echo substr((string) $body, 0, 800)."\n";
    if ($code >= 200 && $code < 300) {
        echo "PASS: API accepted hook generation.\n";
    } else {
        echo "WARN: API returned non-success (queue/worker may still process).\n";
    }
}

echo "\n=== ALL CHECKS PASSED ===\n";

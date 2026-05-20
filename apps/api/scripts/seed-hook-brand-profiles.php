<?php

declare(strict_types=1);

/**
 * Seeds three contrasting brand profiles for hook personalization testing.
 *
 * Usage: php scripts/seed-hook-brand-profiles.php <workspace-uuid>
 */
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Brand\Contracts\BrandMemoryContextServiceContract;
use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Brand\Data\AudienceProfileDto;
use App\Domains\Brand\Data\CreateBrandProfileDto;
use App\Domains\Brand\Data\CreateWritingSampleDto;
use App\Domains\Brand\Data\StyleGuidelinesDto;
use App\Domains\Brand\Data\ToneProfileDto;
use App\Domains\Brand\Enums\WritingSampleSourceType;
use App\Domains\Brand\Services\BrandMemoryOrchestrationService;

$workspaceId = $argv[1] ?? null;

if ($workspaceId === null) {
    $workspaceId = \Illuminate\Support\Facades\DB::table('workspaces')->value('id');
}

if ($workspaceId === null) {
    fwrite(STDERR, "Usage: php scripts/seed-hook-brand-profiles.php <workspace-uuid>\n");
    exit(1);
}

$profiles = app(BrandProfileRepositoryContract::class);
$ingest = app(BrandMemoryOrchestrationService::class);
$contextService = app(BrandMemoryContextServiceContract::class);

$presets = [
    'aggressive_founder' => [
        'name' => 'Aggressive Founder',
        'brand_voice' => 'Blunt, high-conviction, no fluff. Challenge the reader.',
        'tone' => new ToneProfileDto(primary: 'bold', traits: ['direct', 'provocative'], avoid: ['corporate', 'passive']),
        'audience' => new AudienceProfileDto(summary: 'Early-stage B2B founders scaling past $1M ARR'),
        'banned' => ['synergy', 'leverage', 'game-changing', 'revolutionary'],
        'ctas' => ['DM me "scale"', 'Grab the playbook'],
        'patterns' => ['contrarian claim', 'number-led hook'],
        'style' => new StyleGuidelinesDto(summary: 'Short punchy sentences. One idea per line.', maxHookLength: 120),
        'sample' => "Most founders are optimizing the wrong metric.\n\nWe 3x'd pipeline in 90 days by killing 'brand awareness' posts.\n\nHere's the playbook:",
    ],
    'corporate' => [
        'name' => 'Corporate Enterprise',
        'brand_voice' => 'Professional, measured, credibility-first. Evidence over hype.',
        'tone' => new ToneProfileDto(primary: 'professional', traits: ['authoritative', 'clear'], avoid: ['slang', 'hype']),
        'audience' => new AudienceProfileDto(summary: 'Enterprise RevOps and marketing leaders'),
        'banned' => ['crushing it', 'hustle', '🔥'],
        'ctas' => ['Download the executive brief', 'Schedule a briefing'],
        'patterns' => ['industry statistic opener', 'framework introduction'],
        'style' => new StyleGuidelinesDto(summary: 'Formal but accessible. Cite data where possible.', useEmojis: false),
        'sample' => "Organizations that align marketing and revenue operations report 24% higher win rates.\n\nOur latest benchmark study outlines three governance practices that reduce funnel leakage.",
    ],
    'gen_z_creator' => [
        'name' => 'Gen-Z Creator',
        'brand_voice' => 'Casual, relatable, story-first. Speak like a friend who figured it out.',
        'tone' => new ToneProfileDto(primary: 'conversational', traits: ['playful', 'authentic'], avoid: ['corporate jargon']),
        'audience' => new AudienceProfileDto(summary: 'Creators and solo builders on LinkedIn'),
        'banned' => ['utilize', 'stakeholders', 'paradigm'],
        'ctas' => ['Comment "yes" for the template', 'Save this for later'],
        'patterns' => ['personal story cold open', 'relatable question'],
        'style' => new StyleGuidelinesDto(summary: 'Conversational pacing. Rhetorical questions welcome.', useEmojis: true),
        'sample' => "ok real talk — I used to overthink every post for HOURS 😅\n\nThen I tried this dumb-simple hook formula and my impressions literally doubled.\n\nwant me to break it down?",
    ],
];

echo "Workspace: {$workspaceId}\n\n";

foreach ($presets as $key => $preset) {
    $profile = $profiles->create(new CreateBrandProfileDto(
        workspaceId: $workspaceId,
        name: $preset['name'],
        brandVoice: $preset['brand_voice'],
        toneProfile: $preset['tone'],
        targetAudience: $preset['audience'],
        bannedPhrases: $preset['banned'],
        preferredCtas: $preset['ctas'],
        preferredHookPatterns: $preset['patterns'],
        styleGuidelines: $preset['style'],
        metadata: ['preset' => $key, 'experiment_id' => "preset:{$key}"],
        isPrimary: $key === 'aggressive_founder',
    ));

    $ingest->ingestWritingSample(new CreateWritingSampleDto(
        workspaceId: $workspaceId,
        content: $preset['sample'],
        sourceType: WritingSampleSourceType::Manual,
        brandProfileId: $profile->id,
        metadata: ['preset' => $key],
    ));

    $ctx = $contextService->forHookAgent($workspaceId, 'test hook about growth', null, null);

    echo "=== {$preset['name']} ({$key}) ===\n";
    echo "Profile ID: {$profile->id}\n";
    echo "Compact section (".strlen($ctx->compactBrandSection)." chars):\n";
    echo $ctx->compactBrandSection."\n\n";
}

echo "Done. Set is_primary on desired profile for live runs.\n";

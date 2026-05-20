<?php

declare(strict_types=1);

/**
 * Verifies brand profile API persistence + writing samples.
 *
 * Usage: php scripts/verify-brand-profile-api.php <workspace-uuid>
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$workspaceId = $argv[1] ?? null;
if (! is_string($workspaceId) || $workspaceId === '') {
    fwrite(STDERR, "Usage: php scripts/verify-brand-profile-api.php <workspace-uuid>\n");
    exit(1);
}

$mgmt = app(\App\Domains\Brand\Services\BrandProfileManagementService::class);

echo "=== Brand Profile API verification ===\n\n";

$profile = $mgmt->getOrCreatePrimary($workspaceId);
echo "Primary profile: {$profile->id} (v{$profile->memoryVersion})\n";

$updated = $mgmt->updateProfile($workspaceId, $profile->id, new \App\Domains\Brand\Data\UpdateBrandProfileDto(
    brandVoice: 'Verify API voice — blunt and operator-focused.',
    toneProfile: new \App\Domains\Brand\Data\ToneProfileDto(
        primary: 'bold',
        traits: ['direct', 'provocative'],
        avoid: ['corporate'],
    ),
    bannedPhrases: ['game-changing', 'synergy', 'revolutionary'],
    preferredCtas: ['DM me "verify"'],
    targetAudience: new \App\Domains\Brand\Data\AudienceProfileDto(
        summary: 'B2B founders testing personalization',
        segments: ['founders'],
    ),
));

$reloaded = $mgmt->getProfile($workspaceId, $profile->id);
assert($reloaded !== null, 'Profile missing after update');
assert(str_contains($reloaded->brandVoice, 'Verify API'), 'brand_voice not persisted');
assert(in_array('game-changing', $reloaded->bannedPhrases, true), 'banned_phrases not persisted');
assert($reloaded->toneProfile->primary === 'bold', 'tone not persisted');
assert($reloaded->targetAudience->summary !== '', 'audience not persisted');
echo "PASS: profile fields persisted (memory v{$reloaded->memoryVersion})\n";

$sample = $mgmt->createWritingSample(new \App\Domains\Brand\Data\CreateWritingSampleDto(
    workspaceId: $workspaceId,
    content: 'Most founders optimize the wrong metric. We tripled pipeline in 90 days without hiring.',
    brandProfileId: $profile->id,
    sourceType: \App\Domains\Brand\Enums\WritingSampleSourceType::Manual,
));
echo "PASS: writing sample created {$sample->id}\n";

$samples = $mgmt->listWritingSamples($workspaceId, $profile->id);
assert(count($samples) >= 1, 'samples list empty');
echo 'PASS: '.count($samples)." sample(s) listed\n";

$mgmt->updateWritingSample($workspaceId, $sample->id, new \App\Domains\Brand\Data\UpdateWritingSampleDto(
    content: 'Updated sample: short sentences. No fluff. Ship weekly.',
    reextractStyle: true,
));
echo "PASS: sample updated\n";

$mgmt->deleteWritingSample($workspaceId, $sample->id);
$afterDelete = $mgmt->listWritingSamples($workspaceId, $profile->id);
$stillThere = array_filter($afterDelete, static fn ($s) => $s->id === $sample->id);
assert($stillThere === [], 'sample not deleted');
echo "PASS: sample deleted\n";

$preview = $mgmt->memoryPreview($workspaceId, 'AI automation for founders');
assert($preview->compactBrandSection !== '', 'memory preview empty');
assert(in_array('game-changing', $preview->bannedPhrases, true), 'banned phrases not in preview');
echo 'PASS: memory preview ('.strlen($preview->compactBrandSection)." chars)\n";

echo "\nAll brand profile API checks passed.\n";

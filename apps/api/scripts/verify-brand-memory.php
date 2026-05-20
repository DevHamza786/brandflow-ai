<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Brand\Contracts\BrandMemoryQueryServiceContract;
use App\Domains\Brand\Models\BrandProfile;
use App\Domains\Brand\Models\WritingSample;
use App\Domains\Brand\Services\WritingStyleExtractionService;
use App\Domains\Brand\Support\BrandMemoryNormalizer;
use Illuminate\Support\Facades\DB;

$ws = DB::table('workspaces')->value('id');
if ($ws === null) {
    fwrite(STDERR, "No workspace found\n");
    exit(1);
}

$profile = BrandProfile::query()->create([
    'workspace_id' => $ws,
    'name' => 'Memory test',
    'brand_voice' => 'Direct and bold',
    'tone_profile' => ['primary' => 'bold', 'traits' => ['confident']],
    'target_audience' => ['summary' => 'B2B founders'],
    'banned_phrases' => ['synergy', 'leverage'],
    'preferred_ctas' => ['Book a call'],
    'preferred_hook_patterns' => ['question opener'],
    'style_guidelines' => ['summary' => 'Short sentences'],
    'metadata' => ['experiment_id' => 'exp-1'],
    'voice' => [],
    'pillars' => [],
    'constraints' => [],
]);

$fresh = $profile->fresh();
assert(is_array($fresh->tone_profile));
assert(is_array($fresh->banned_phrases));
assert(count($fresh->banned_phrases) === 2);

$normalizer = app(BrandMemoryNormalizer::class);
$dto = $normalizer->normalizeProfile($fresh);
assert($dto->toneProfile->primary === 'bold');
assert(count($dto->bannedPhrases) === 2);

$style = app(WritingStyleExtractionService::class)->extract(
    'I help founders scale. What if you could 2x pipeline?'
);

WritingSample::query()->create([
    'workspace_id' => $ws,
    'brand_profile_id' => $profile->id,
    'content' => 'I help founders scale. What if you could 2x pipeline?',
    'source_type' => 'manual',
    'normalized_style_data' => $style->toArray(),
    'embedding_ready' => true,
]);

$enrichment = app(BrandMemoryQueryServiceContract::class)->enrichForWorkspace($ws, 'founders');
assert(count($enrichment->memoryChunks) >= 1);
assert(isset($enrichment->promptVariables['brand_voice']));

echo "brand_memory_verification_ok\n";

<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Agents\Agents\HookAgent\Support\HookBannedPhraseFilter;
use App\Domains\Brand\Contracts\BrandMemoryContextServiceContract;
use App\Domains\Brand\Data\CreateBrandProfileDto;
use App\Domains\Brand\Data\ToneProfileDto;
use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Agents\Agents\HookAgent\Data\HookVariant;
use App\Domains\Agents\Agents\HookAgent\Data\HookScoreDimensions;

$ws = \Illuminate\Support\Facades\DB::table('workspaces')->value('id');
$profiles = app(BrandProfileRepositoryContract::class);
$contextService = app(BrandMemoryContextServiceContract::class);
$filter = app(HookBannedPhraseFilter::class);

$profile = $profiles->create(new CreateBrandProfileDto(
    workspaceId: $ws,
    name: 'Banned phrase test',
    brandVoice: 'Test voice',
    toneProfile: new ToneProfileDto(primary: 'professional'),
    bannedPhrases: ['revolutionary', 'game-changing'],
    isPrimary: true,
));

$ctx = $contextService->forHookAgent($ws, 'How to grow on LinkedIn', null, null);

assert(str_contains($ctx->compactBrandSection, 'Never use'));
assert(str_contains($ctx->compactBrandSection, 'revolutionary'));
assert(strlen($ctx->compactBrandSection) <= (int) config('ai.hook_agent.max_compact_section_chars', 1200) + 10);

$variants = [
    new HookVariant(
        text: 'This revolutionary game-changing hook will transform your career.',
        overall: 80,
        dimensions: new HookScoreDimensions(),
    ),
];

$filtered = $filter->filterVariants($variants, $ctx->bannedPhrases);
assert(count($filtered) === 1);
assert(! str_contains(strtolower($filtered[0]->text), 'revolutionary'));
assert(! str_contains(strtolower($filtered[0]->text), 'game-changing'));

echo "hook_brand_memory_integration_ok\n";
echo "compact_chars=".strlen($ctx->compactBrandSection)."\n";
echo "sanitized_variant=".$filtered[0]->text."\n";

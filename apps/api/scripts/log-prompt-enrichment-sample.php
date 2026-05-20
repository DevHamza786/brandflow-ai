<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['ai.hook_agent.log_prompt_enrichment' => true]);

$ws = '9bb59c64-347a-48b3-b010-e41b5cdc6f4d';
$ctx = app(\App\Domains\Brand\Contracts\BrandMemoryContextServiceContract::class)
    ->forHookAgent($ws, 'AI automation LinkedIn growth', 'founders', null);

$prompts = app(\App\Domains\AI\Contracts\PromptTemplateRegistryContract::class);
$vars = array_merge($ctx->promptVariables, [
    'hook_text' => 'AI automation is changing LinkedIn growth.',
    'compact_brand_memory' => $ctx->compactBrandSection,
    'content_pillar' => '',
]);

$prompt = $prompts->render('hook.scorer', $vars, 'v1');

app(\App\Domains\Agents\Agents\HookAgent\Support\HookPersonalizationLogger::class)
    ->logPromptEnrichment('score', $ws, 'test-trace', $ctx, $prompt);

echo "Prompt length: ".strlen($prompt)."\n\n";
echo $prompt."\n";

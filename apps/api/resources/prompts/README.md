# Prompt Templates

Prompts are Blade files only — never inline strings in PHP agents or controllers.

## Path convention

Slug `hook.scorer` + version `v1`:

```text
resources/prompts/hook/scorer/v1.blade.php
```

View name: `prompts::hook.scorer.v1`

## Header comment (required variables)

```blade
{{-- prompt: hook.scorer v1 --}}
{{-- variables: @var string $draft_text @var array $memory_chunks --}}
```

## Usage

```php
$registry->render('hook.scorer', [
    'draft_text' => $text,
    'memory_chunks' => $chunks,
], 'v1');

$gateway->complete(new LlmRequest(
    workspaceId: $workspaceId,
    provider: 'openai',
    model: 'gpt-4o-mini',
    messages: [new AiMessage(AiMessageRole::User, $prompt)],
    structuredOutput: StructuredOutputConfig::jsonObject(),
));
```

See [docs/AGENTS.md](../../../docs/AGENTS.md) §8.

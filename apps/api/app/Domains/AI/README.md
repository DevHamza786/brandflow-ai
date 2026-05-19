# AI Domain

Scalable LLM infrastructure for PBOS — provider abstraction, prompts, memory injection, structured outputs.

## Architecture

```text
Application / Agents
        │
        ▼
  LlmGateway (interface)
        │
        ▼
  LlmGatewayService ── MemoryPromptAssembler
        │              RetryExecutor
        ▼
  LlmProviderFactory
        │
   ┌────┴────┐
   ▼         ▼
OpenAi    Gemini
Adapter   Adapter
```

## Rules

- **No direct vendor HTTP calls** outside `Adapters/`.
- Inject `LlmGateway` — never `OpenAiAdapter` in controllers or agents.
- Use DTOs: `LlmRequest`, `AiResponse`, `TokenUsage`, `MemoryContext`.
- Prompts via `PromptTemplateRegistry` + Blade under `resources/prompts/`.

## Configuration

`config/ai.php` — providers, retry, prompts, memory preamble.

| Env | Purpose |
|-----|---------|
| `OPENAI_API_KEY` | OpenAI |
| `GEMINI_API_KEY` | Google Gemini |
| `AI_DEFAULT_PROVIDER` | `openai` (default) |
| `AI_FALLBACK_PROVIDER` | `gemini` |
| `AI_ENABLE_FALLBACK` | `true` |
| `AI_USE_NULL_GATEWAY` | `true` for tests without APIs |

## Key classes

| Class | Role |
|-------|------|
| `Contracts/LlmGateway` | Application entry point |
| `Services/LlmGatewayService` | Orchestration, retry, fallback, memory |
| `Factories/LlmProviderFactory` | Resolves provider adapters |
| `Adapters/OpenAiAdapter` | OpenAI HTTP |
| `Adapters/GeminiAdapter` | Gemini HTTP |
| `Services/PromptTemplateRegistry` | Template resolution + render |
| `Services/PromptRenderer` | Blade rendering |
| `Support/RetryExecutor` | Exponential backoff |
| `Support/StructuredOutputDecoder` | JSON output parsing |

## Exceptions

`AiException` → `ProviderException`, `ProviderRateLimitException`, `ProviderTimeoutException`, `StructuredOutputException`, `PromptTemplateNotFoundException`, `ProviderNotConfiguredException`

## Related docs

- [docs/AGENTS.md](../../../docs/AGENTS.md)
- [docs/PROJECT_ARCHITECTURE.md](../../../docs/PROJECT_ARCHITECTURE.md) §5

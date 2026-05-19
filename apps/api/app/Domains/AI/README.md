# AI Domain

LLM provider abstraction, prompt templates, embeddings, and brand memory retrieval.

## Responsibilities

- `LlmGateway` — sole entry point for OpenAI / Gemini calls
- `PromptTemplateRegistry` — versioned prompt resolution and rendering
- `MemoryRetrievalService` — RAG over brand memory (implemented later)
- `TokenBudgetService` — per-workspace usage limits (implemented later)

## Structure

```
AI/
├── Contracts/       # LlmGateway, PromptTemplateRegistry
├── Data/            # LlmRequest, LlmResponse, EmbedRequest, EmbedResponse
├── Services/        # Orchestration (no inline prompts)
├── Adapters/        # OpenAiAdapter, GeminiAdapter, NullLlmGateway
└── Support/         # Shared AI utilities
```

## Rules

- No LLM calls outside `LlmGateway`.
- No prompt strings in services, agents, or controllers.
- Prompt files live in `resources/prompts/`.

## Related docs

- [docs/AGENTS.md](../../../docs/AGENTS.md) §8 Prompt Engineering
- [docs/PROJECT_ARCHITECTURE.md](../../../docs/PROJECT_ARCHITECTURE.md) §5 AI Pipeline

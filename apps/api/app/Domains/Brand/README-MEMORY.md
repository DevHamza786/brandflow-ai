# Brand Memory Foundation

Workspace-scoped personalization for AI workflows — RAG-ready, provider-agnostic.

## Layout (`App\Domains\Brand`)

| Layer | Responsibility |
|-------|----------------|
| `Models/` | `BrandProfile`, `WritingSample` (thin Eloquent) |
| `Data/` | DTOs — `BrandProfileDto`, `ToneProfileDto`, `BrandMemoryEnrichmentDto`, … |
| `Repositories/` | Workspace-scoped persistence |
| `Services/` | `BrandMemoryQueryService`, `BrandMemoryEnrichmentService`, `WritingStyleExtractionService`, `BrandMemoryOrchestrationService` |
| `Support/` | `BrandMemoryNormalizer`, `BrandMemorySerializer`, `BrandMemoryPromptInjector` |

## Tables

- `brand_profiles` — voice, tone, audience, banned phrases, CTAs, hook patterns, style guidelines
- `writing_samples` — content, `normalized_style_data`, `embedding_ready` flag

## Prompt injection

`BrandMemoryPromptInjector` → `promptVariables` + `brand_memory_section` for Blade prompts.

`MemoryRetrievalService` returns `MemoryContext` with chunk references for `MemoryPromptAssembler`.

## Hook Agent integration

| Component | Role |
|-----------|------|
| `BrandMemoryContext` | Compact prompt bundle + persistence chunks |
| `BrandMemoryContextService::forHookAgent()` | Pipeline with selection + anti-bloat limits |
| `HookAgentMemoryEnrichmentService` | HookAgent entry point |
| `HookBrandMemoryPromptComposer` | Tone, audience, banned phrases, CTAs, style signals |
| `HookBannedPhraseFilter` | Post-processes generated variants |
| `HookPersonalizationLogger` | `HOOK_LOG_PROMPT_ENRICHMENT=true` logs full context |

Config: `config/ai.php` → `hook_agent` (max 1200 char compact section, 3 chunks max).

Test: `php scripts/seed-hook-brand-profiles.php <workspace-uuid>`

## Future

- Vector search: set `embedding_ready`, index via pgvector / Redis
- Personalization engine: consume `BrandMemoryEnrichmentDto::analyticsPayload`
- A/B: `metadata.experiment_id` / `experiment_variant` placeholders

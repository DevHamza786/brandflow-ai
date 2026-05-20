# Brand Domain

Brand profiles, voice, pillars, brand memory chunks, writing samples, and profile optimization records.

## Memory foundation

See [README-MEMORY.md](./README-MEMORY.md) for personalization DTOs, enrichment, prompt injection, and writing style extraction.

## Repository contracts

- `BrandProfileRepositoryContract`
- `WritingSampleRepositoryContract`
- `MemoryChunkRepositoryContract`
- `MemorySourceRepositoryContract`
- `ProfileOptimizationRepositoryContract`

## Services

- `BrandMemoryQueryService` — read / enrich for workspace
- `BrandMemoryEnrichmentService` — prompt variables + memory chunks
- `WritingStyleExtractionService` — normalized style signals
- `BrandMemoryOrchestrationService` — sample ingest + version bump
- `MemoryRetrievalService` — `MemoryContext` for agents

## Related agents

- `ProfileAgent` (primary)
- Memory retrieval consumed by all agents via AI domain

## Related docs

- [docs/PROJECT_ARCHITECTURE.md](../../../../docs/PROJECT_ARCHITECTURE.md) §6 Memory System

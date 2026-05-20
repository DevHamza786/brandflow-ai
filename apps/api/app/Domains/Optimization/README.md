# Optimization Loop Domain

Adaptive intelligence for BrandFlow AI: **generate → publish → observe → analyze → optimize** cycles without autonomous publishing.

## Tables

| Table | Purpose |
|-------|---------|
| `optimization_loops` | Workspace-scoped loop state (`current_cycle`, `ml_state`, config) |
| `optimization_snapshots` | Per-cycle engine observations with baseline/observed/delta metrics |

## Flow

```
POST /api/v1/optimization/cycles/run
  → RunOptimizationCycleAction
  → OptimizationOrchestrationService
  → OptimizationEngine
      → OptimizationAnalyticsIntegration (period-split snapshots)
      → Hook / PostingTime / CTA / AudienceFit engines
      → OptimizationSnapshotRepository
      → OptimizationRecommendationBridge → recommendations
```

## Engines

| Engine | Focus | Data source |
|--------|-------|-------------|
| `hook_structure` | Winning hook styles | Period-over-period `post_performance_snapshots` |
| `posting_time` | Best UTC hour | `postingHourHistogram` |
| `cta` | Preferred CTA uplift | Brand profile + snapshots |
| `audience_fit` | Hook Lab dimension gaps | Dimension averages top vs bottom quartile |

## Future compatibility

- `ml_features` / `ml_state` stubs for embeddings, bandits, RL policies
- Events: `OptimizationCycleStarted`, `OptimizationCycleCompleted` for workflow chaining
- No LLM calls in this domain — analytics-safe, repository-only persistence

## Config

See `config/optimization.php`.

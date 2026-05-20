# Recommendations (backend intelligence)

Analytics-driven optimization recommendations for the **generate → publish → observe → optimize** loop. No dashboards; no LLM fluff — evidence from `post_performance_snapshots` and brand profiles.

## Adaptive loop

```
Hook Lab / Publish → snapshots + events (Analytics)
        ↓
RecommendationEngine (correlation + scoring)
        ↓
persist recommendations → RecommendationsGenerated event
        ↓
Agents / workflows / future RL policy consume action_payload + ml_state
```

## Tables

| Table | Role |
|-------|------|
| `recommendations` | Workspace-scoped, scored, evidence JSON, personalization + ML stubs |

## Services

| Service | Role |
|---------|------|
| `RecommendationEngine` | Orchestrates generators + persistence |
| `AnalyticsCorrelationEngine` | Snapshot baselines, percentiles, style buckets |
| `HookStyleCorrelationEngine` | Style uplift vs baseline (moat patterns) |
| `HookOptimizationRecommender` | Best styles + weak-hook rewrites |
| `PostingTimeRecommender` | Hour-of-day from analytics histogram |
| `AudienceFitRecommender` | Dimension gaps vs brand ICP |
| `CtaOptimizationRecommender` | Preferred CTA gaps on low performers |
| `OptimizationOpportunityDetector` | Cadence, personalization, low-engagement clusters |
| `RecommendationScoringService` | Confidence from sample size + uplift |
| `RecommendationAggregationService` | Dedupe by `correlation_key` |
| `RecommendationOrchestrationService` | API/workflow entry + event |
| `RecommendationQueryService` | Read layer |
| `MlCompatibilityLayerContract` | `ml_state` for embeddings / RL / bandits |

## API

| Method | Path |
|--------|------|
| `POST` | `/api/v1/recommendations/generate` |
| `GET` | `/api/v1/recommendations` |
| `GET` | `/api/v1/recommendations/{id}` |

Headers: `X-Workspace-Id`. Optional body: `{ "lookback_days": 90 }`.

## Config

`config/recommendations.php` — lookback, min samples, uplift thresholds.

## Verification

```bash
php artisan migrate
php artisan test tests/Feature/RecommendationEngineTest.php
```

## Future

- Swap `HookStyleClassifier` for embedding clusters
- Wire `RecommendationsGenerated` → workflow steps / HookAgent context
- Reward signals already stamped on `ml_state.reward_signal`

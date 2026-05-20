# Analytics Intelligence (backend)

Event-driven ingestion + performance snapshots for **generate → publish → observe → learn** loops. No dashboards in this phase.

## Tables

| Table | Role |
|-------|------|
| `analytics_events` | Append-only product stream (`event_type`, `entity_*`, `properties`, idempotency) — partition-ready on `created_at` |
| `post_performance_snapshots` | Denormalized post/hook observations (impressions, likes, comments, reposts, saves, rates, `hook_performance`, `ml_features`) |
| `engagement_metrics` | Daily metric grain (existing) — fed by `EngagementTrackingService` |

## Services (orchestration boundary)

| Service | Responsibility |
|---------|----------------|
| `AnalyticsEventIngestionService` | Append events (idempotent keys) |
| `EngagementTrackingService` | Snapshot + daily metrics + `post.performance_observed` event |
| `PerformanceAggregationService` | Engagement math + hook blend + `ml_features.vector_stub` |
| `EngagementNormalizationService` | Raw rate + `[0,1]` normalization |
| `HookPerformanceScoringEngine` | Lab score × observed engagement blend |
| `BestPerformingVariantAnalyzer` | Top hooks by `normalized_engagement` |
| `PostingTimeAnalyzer` | Hour-of-day histogram (foundation) |
| `RecommendationSignalsService` | Compact DTO for recommenders / RL (see `Recommendations` domain) |
| `AnalyticsQueryService` | Read API for agents & workflows |
| `AnalyticsOrchestrationService` | `recordHookScored`, `recordWorkflowSignal`, accessors |

## Events

- `HookScored` → `IngestHookScoredAnalyticsListener` (registered in `AnalyticsServiceProvider`)

## Verification

```bash
php artisan migrate
php artisan test tests/Unit/EngagementNormalizationServiceTest.php
php artisan test tests/Feature/AnalyticsIntelligenceTest.php
```

Postgres:

```sql
\d analytics_events
\d post_performance_snapshots
```

## Design constraints

- **No extra abstraction layers** beyond repositories + focused services.
- **ML / embeddings**: `FeatureVectorBuilderContract` + `ml_features` JSON only — swap implementation later.
- **Hot paths**: prefer snapshots + rollups over scanning `analytics_events.properties` (GIN index exists for ad-hoc queries only).

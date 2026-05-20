# Intelligence Domain (Competitor)

Competitor tracking and **analytics-driven intelligence** without scraping (ingest via API / manual payload).

## Adaptive growth loop

```
Track competitor → ingest snapshot → analyze patterns
        ↓
Correlate engagement + hook styles → benchmark vs workspace
        ↓
CompetitorRecommendationBridge → recommendations table
        ↓
Hook Lab / workflows / future autonomous strategy
```

## Tables

| Table | Role |
|-------|------|
| `competitors` | Workspace-scoped LinkedIn profiles to watch |
| `competitor_snapshots` | Point-in-time payload + derived analytics columns |

Extension migration `2026_05_26_100000_extend_competitor_intelligence_columns` adds indexed analytics fields (`hook_patterns`, `engagement_metrics`, `ml_features`, etc.).

## Services

| Service | Role |
|---------|------|
| `CompetitorIngestionService` | Idempotent ingest (content hash) |
| `CompetitorAnalyticsService` | Full analysis pipeline on snapshot |
| `HookPatternExtractionEngine` | Style uplift insights (moat patterns) |
| `PostingFrequencyAnalyzer` | Cadence + hour histogram |
| `EngagementBenchmarkingEngine` | vs `post_performance_snapshots` |
| `CompetitorTrendAnalysisService` | Snapshot-over-snapshot deltas |
| `CompetitorScoringEngine` | Composite intelligence score |
| `CompetitorRecommendationBridge` | Writes to `recommendations` |
| `CompetitorOrchestrationService` | Ingest + analyze + sync recs |
| `CompetitorQueryService` | Read model / reports |

## API (no scrape)

| Method | Path |
|--------|------|
| `POST` | `/api/v1/competitors` |
| `GET` | `/api/v1/competitors` |
| `GET` | `/api/v1/competitors/{id}` |
| `POST` | `/api/v1/competitors/{id}/snapshots` |

### Ingest payload shape

```json
{
  "payload": {
    "posts": [
      {
        "hook_text": "Why do SaaS founders…?",
        "published_at": "2026-05-20T10:00:00Z",
        "impressions": 5000,
        "likes": 200,
        "comments": 20,
        "cta_text": "Comment PLAYBOOK"
      }
    ]
  }
}
```

## Events

- `CompetitorSnapshotCaptured` — after ingest + analysis (workflow hook)

## Verification

```bash
docker compose exec api php artisan migrate --force
docker compose exec api php artisan test tests/Feature/CompetitorIntelligenceTest.php
```

Postgres:

```sql
\d competitors
\d competitor_snapshots
```

## Future

- Scrape jobs populate same payload schema
- Embeddings via `CompetitorMlCompatibilityLayerContract`
- `CompetitorAgent` consumes reports (AGENTS.md §4.4)

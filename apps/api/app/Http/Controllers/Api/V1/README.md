# API V1 Controllers

Thin controllers: validate, delegate to domain actions/services, return API resources.

## LinkedIn publish (queue)

| Method | Path | Status |
|--------|------|--------|
| `POST` | `/api/v1/publish/linkedin` | `202` — persists `scheduled_posts`, dispatches `PublishLinkedInPostJob` (`scheduling` queue) |

Body (JSON): `linkedin_integration_id` (uuid, required), `content` or `generated_output_id`, optional `scheduled_for` (ISO 8601).

Example:

```bash
curl -X POST "http://localhost:8080/api/v1/publish/linkedin" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Workspace-Id: {workspaceUuid}" \
  -d '{"linkedin_integration_id":"{integrationUuid}","content":"Hello from curl"}'
```

| Method | Path | Status |
|--------|------|--------|
| `GET` | `/api/v1/scheduled-posts?limit=50` | `200` — `{ scheduled_posts: [...], in_flight: bool }` for activity UI / polling |

## Hook Lab

| Method | Path | Status |
|--------|------|--------|
| `POST` | `/api/v1/content-versions/{versionId}/hooks/generate` | `202` (new) / `200` (idempotent replay) |

Example:

```bash
curl -X POST "http://localhost:8080/api/v1/content-versions/{versionId}/hooks/generate" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Workspace-Id: {workspaceUuid}" \
  -d '{"options":{"max_variants":3,"target_audience":"B2B founders"}}'
```
| `GET` | `/api/v1/agents/runs/{agentRunId}` | `200` |

Headers: `X-Workspace-Id` (required), `Idempotency-Key` (recommended on POST).

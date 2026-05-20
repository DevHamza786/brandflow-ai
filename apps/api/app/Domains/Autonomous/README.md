# Autonomous Posting Domain

Observe → optimize → decide → (future) publish cycle. **Does not auto-publish** in v1 — decisions persist with `action_payload.publish: false`.

## API

- `GET /api/v1/autonomous/workflows`
- `PATCH /api/v1/autonomous/workflows/{id}` — `min_confidence`, `mode`, `manual_override_enabled`
- `GET /api/v1/autonomous/snapshots`
- `POST /api/v1/autonomous/executions/run`

## Safety

- Configurable `min_confidence` (default 0.65)
- Status `blocked_low_confidence` when below threshold
- Workflow lock during cycle (`lock_token`, TTL)
- Unique `idempotency_key` per decision

## Config

`config/autonomous.php`

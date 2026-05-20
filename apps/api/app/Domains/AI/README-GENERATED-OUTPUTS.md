# Generated Outputs

Unified persistence for AI agent / workflow artifacts. Complements domain-specific tables (e.g. `hook_scores`) with a cross-agent analytics and retrieval store.

## JSON casting strategy

| Column | Storage | Access |
|--------|---------|--------|
| `input` | jsonb | `GeneratedOutputInputDto` — request/agent context |
| `output` | jsonb | `GeneratedOutputPayloadDto` — structured LLM result |
| `scores` | jsonb | `GeneratedOutputScoresDto` — rubric / dimensions |
| `metadata` | jsonb | `GeneratedOutputMetadataDto` — trace, RAG, fine-tuning |

Eloquent uses native `'array'` casts on the thin model. Services/repositories map to DTOs at boundaries.

## Query optimization

1. **Always filter by `workspace_id` first** — all composite indexes lead with it.
2. **Time-series analytics** — `WHERE workspace_id = ? AND created_at BETWEEN ? AND ? ORDER BY created_at DESC`.
3. **Agent / workflow correlation** — use `agent_run_id` or `workflow_run_id` indexes, not full table scans.
4. **Metadata keyed lookup** — PostgreSQL GIN (`metadata jsonb_path_ops`) for `embedding_id`, `memory_chunk_ids`, etc.
5. **List endpoints** — `paginateForWorkspace()` with `type`, `status`, `provider` filters; avoid `SELECT *` on large `output` when listing — use `GeneratedOutputSummaryResource`.

## Async safety

- Unique `(workspace_id, agent_run_id, type)` when `agent_run_id` is set.
- `upsertForAgentRun()` for idempotent queue retries.
- Status lifecycle: `pending` → `processing` → `completed` | `failed` | `superseded`.

## Workflow integration

```php
// 1. Before dispatching agent job
$output = $persistAction->begin(new CreateGeneratedOutputDto(...));

// 2. On completion (queue worker)
$finalizeAction->complete($wsId, $output->id, GeneratedOutputPayloadDto::fromArray($result));

// 3. Link workflow context
$workflowBridge->attachToWorkflowContext($wsId, $workflowRunId, $output);
```

Contracts: `GeneratedOutputPersistenceContract`, `WorkflowGeneratedOutputContract`.

## Events

- `GeneratedOutputPersisted` — row reserved
- `GeneratedOutputCompleted` — analytics / workflow listeners
- `GeneratedOutputFailed` — failure handling

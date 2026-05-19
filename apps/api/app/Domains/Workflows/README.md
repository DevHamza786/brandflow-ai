# Workflows Domain

DAG definitions, workflow runs, human approval gates, and multi-agent orchestration.

## Repository contracts

- `WorkflowRepositoryContract`
- `WorkflowRunRepositoryContract`

## Rules

- Prefer workflows over direct agent-to-agent calls.
- Do not chain more than 5 jobs without a `workflow_run` record.

## Related docs

- [docs/AGENTS.md](../../../docs/AGENTS.md) §4.7 Agent interaction matrix
- [docs/PROJECT_ARCHITECTURE.md](../../../docs/PROJECT_ARCHITECTURE.md) §4.3 Workflow Orchestration

# AI Workflow Builder

Backend graph infrastructure for visual/no-code workflow compatibility.

## Tables

- `workflow_blueprints` — versioned blueprint definitions
- `workflow_nodes` — agent, delay, condition, optimization, autonomous, coordination nodes
- `workflow_edges` — directed edges with optional conditions

## Core services

| Service | Role |
|---------|------|
| `WorkflowBuilderEngine` | Validate + execute blueprint graphs |
| `WorkflowGraphOrchestrator` | Compile DAG + topological order |
| `WorkflowValidationEngine` | Cycle/orphan detection |
| `NodeExecutionEngine` | Per-node handler (delegates to Coordination, Optimization, Autonomous, Agents) |

## API

- `GET /api/v1/workflow-builder/blueprints`
- `GET /api/v1/workflow-builder/blueprints/{id}`
- `GET /api/v1/workflow-builder/blueprints/{id}/validate`
- `POST /api/v1/workflow-builder/blueprints/{id}/execute`
- `POST /api/v1/workflow-builder/execute` (default blueprint)

## Integration

Preserves existing `workflow_runs` by creating bridge runs on execution. Multi-agent flows use `coordination` node type → `RunCoordinationCycleAction`.

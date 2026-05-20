# Multi-Agent Coordination

Backend infrastructure for BrandFlow AI multi-agent orchestration.

## Tables

- `agent_coordinations` — workspace coordination sessions
- `agent_coordination_snapshots` — routing, context-share, dispatch, failure, recovery audit

## Core services

| Service | Role |
|---------|------|
| `MultiAgentCoordinator` | Cycle orchestration with failure isolation |
| `AgentRoutingEngine` | Task → agent slug / integration handler |
| `AgentPriorityEngine` | Task ordering (sequential / strategist-led) |
| `AgentContextOrchestrator` | Reference-only shared context (no prompt duplication) |
| `InterAgentCommunicationLayer` | Agent queue + optimization/recommendation/publishing integrations |
| `WorkflowSharingEngine` | Workflow run / blueprint refs |
| `AgentMemorySynchronization` | Memory chunk ID refs in agent options |

## API

- `GET /api/v1/coordination/sessions`
- `GET /api/v1/coordination/snapshots`
- `POST /api/v1/coordination/cycles/run`
- `GET /api/v1/coordination/routing/preview`

## Config

`config/coordination.php` — role routing, default cycle tasks, `COORDINATION_DISPATCH_AGENTS`.

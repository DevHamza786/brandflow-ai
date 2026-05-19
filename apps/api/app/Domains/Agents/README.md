# Agents Domain

Autonomous AI workers (Hook, Profile, Analytics, Competitor, Reply, Carousel) orchestrated via queues.

## Responsibilities

- `AgentContract` implementations (one folder per agent, isolated)
- `RunAgentJob` — single queue entry point per run
- `AgentRunner` / `AgentRunService` — orchestration (implemented later)
- `AgentToolRegistry` — tool dispatch for agents (implemented later)
- Persistence via `AgentRunRepository`, `AgentStepRepository`

## Agent slugs

| Slug | Class path (when implemented) | Queue |
|------|-------------------------------|-------|
| `hook` | `Agents/HookAgent/HookAgent` | `ai` |
| `profile` | `Agents/ProfileAgent/ProfileAgent` | `ai` |
| `analytics` | `Agents/AnalyticsAgent/AnalyticsAgent` | `analytics` |
| `competitor` | `Agents/CompetitorAgent/CompetitorAgent` | `ai` |
| `reply` | `Agents/ReplyAgent/ReplyAgent` | `ai` |
| `carousel` | `Agents/CarouselAgent/CarouselAgent` | `ai` |

## Rules

- Agents must not import other agents.
- Agents use tools and domain services; no direct Eloquent in agent classes.
- All runs are async via `RunAgentJob` unless explicitly documented otherwise.

## Related docs

- [docs/AGENTS.md](../../../../docs/AGENTS.md)
- [config/agents.php](../../../config/agents.php)

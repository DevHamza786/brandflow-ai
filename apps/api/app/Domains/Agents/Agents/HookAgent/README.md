# HookAgent

**Slug:** `hook`  
**Queue:** `ai`

Scores opening lines and generates hook variants. See [docs/AGENTS.md](../../../../../docs/AGENTS.md) §4.1.

Implemented: `HookAgent.php`, `HookAgentConfig.php`, `RunHookAgentJob`, `GenerateHooksAction`.

Queue: `RunHookAgentJob` → `AgentRunner` → `HookAgent` → `HookScoringService` + `HookGenerationService` → `LlmGateway`.

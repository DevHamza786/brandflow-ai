import { apiGet } from '@/shared/api/client';
import { asArray, asRecord } from '@/shared/api/normalize';
import type {
  AgentRunDetail,
  AgentRunResults,
  GeneratedOutput,
  HookVariant,
  WorkflowPollingStatus,
} from '@/shared/types/api';

function normalizeResults(raw: AgentRunResults): AgentRunResults {
  return {
    status: raw.status as WorkflowPollingStatus,
    outputs: asArray<GeneratedOutput>(raw.outputs).map(normalizeOutput),
    scores: asRecord(raw.scores),
    metadata: asRecord(raw.metadata),
    variants: asArray<HookVariant>(raw.variants),
    dimensions: asRecord(raw.dimensions) as AgentRunResults['dimensions'],
    suggestions: asArray<string>(raw.suggestions),
    error: raw.error && typeof raw.error === 'object' ? (raw.error as Record<string, unknown>) : null,
    timestamps: raw.timestamps ?? {},
  };
}

function normalizeOutput(output: GeneratedOutput): GeneratedOutput {
  return {
    ...output,
    variants: asArray<HookVariant>(output.variants),
    dimensions: asRecord(output.dimensions) as GeneratedOutput['dimensions'],
    suggestions: asArray<string>(output.suggestions),
    scores: asRecord(output.scores),
    metadata: asRecord(output.metadata),
  };
}

export async function fetchAgentRunDetail(agentRunId: string): Promise<AgentRunDetail> {
  return apiGet<AgentRunDetail>(`/agents/runs/${agentRunId}`);
}

export async function fetchAgentRunResults(agentRunId: string): Promise<AgentRunResults> {
  const data = await apiGet<AgentRunResults>(`/agents/runs/${agentRunId}/results`);
  return normalizeResults(data);
}

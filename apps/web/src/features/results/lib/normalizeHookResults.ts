import { dimensionLabel } from '@/features/results/constants/dimension-labels';
import { rankHookVariants } from '@/features/results/lib/sortVariants';
import type {
  HookResultsMetadata,
  HookResultsTimestamps,
  HookResultsViewModel,
  ScoreDimensionRow,
} from '@/features/results/types/results.types';
import { formatWorkflowError } from '@/features/workflow/lib/formatWorkflowError';
import { isInFlightWorkflowStatus } from '@/features/workflow/lib/polling';
import type { AgentRunDetail, AgentRunResults } from '@/shared/types/api';

function safeNumber(value: unknown): number | null {
  if (typeof value === 'number' && Number.isFinite(value)) return value;
  const n = Number(value);
  return Number.isFinite(n) ? n : null;
}

function parseDimensions(raw: Record<string, unknown> | undefined): ScoreDimensionRow[] {
  if (!raw || typeof raw !== 'object') return [];

  return Object.entries(raw)
    .map(([key, value]) => ({
      key,
      label: dimensionLabel(key),
      value: safeNumber(value) ?? 0,
    }))
    .filter((row) => row.value > 0 || Object.keys(raw).length <= 6)
    .sort((a, b) => b.value - a.value);
}

function collectVariants(results: AgentRunResults): unknown[] {
  if (Array.isArray(results.variants) && results.variants.length > 0) {
    return results.variants;
  }

  for (const output of results.outputs ?? []) {
    if (Array.isArray(output?.variants) && output.variants.length > 0) {
      return output.variants;
    }
  }

  return [];
}

function collectDimensions(results: AgentRunResults): ScoreDimensionRow[] {
  const fromTop = parseDimensions(results.dimensions as Record<string, unknown>);
  if (fromTop.length > 0) return fromTop;

  const scoresDims = results.scores?.dimensions;
  if (scoresDims && typeof scoresDims === 'object') {
    return parseDimensions(scoresDims as Record<string, unknown>);
  }

  const primary = results.outputs[0]?.primary;
  if (primary?.dimensions) {
    return parseDimensions(primary.dimensions as Record<string, unknown>);
  }

  return [];
}

function collectSuggestions(results: AgentRunResults): string[] {
  const top = (results.suggestions ?? []).filter((s) => typeof s === 'string' && s.trim());
  if (top.length > 0) return top;

  const fromOutput = results.outputs[0]?.suggestions ?? [];
  return fromOutput.filter((s) => typeof s === 'string' && s.trim());
}

function buildMetadata(
  results: AgentRunResults,
  detail: AgentRunDetail | undefined,
): HookResultsMetadata {
  const meta = results.metadata ?? {};
  const primaryOutput = results.outputs[0];
  const agentOptions = detail?.agent_run?.options ?? {};

  const experimentId =
    (typeof meta.experiment_id === 'string' && meta.experiment_id) ||
    (typeof agentOptions.experiment_id === 'string' ? agentOptions.experiment_id : null);

  return {
    provider:
      (typeof meta.provider === 'string' && meta.provider) ||
      primaryOutput?.provider ||
      null,
    model:
      (typeof meta.model === 'string' && meta.model) ||
      primaryOutput?.model ||
      null,
    promptVersion:
      (typeof meta.prompt_version === 'string' && meta.prompt_version) ||
      primaryOutput?.prompt_version ||
      null,
    experimentId,
    experimentVariant:
      typeof meta.experiment_variant === 'string' ? meta.experiment_variant : null,
    workflowSlug: detail?.workflow_run?.workflow_slug ?? 'hook_generation',
    agentSlug: detail?.agent_run?.slug ?? 'hook',
    traceId: typeof meta.trace_id === 'string' ? meta.trace_id : detail?.agent_run?.trace_id ?? null,
    outputId: primaryOutput?.id ?? null,
    analyticsReady: true,
  };
}

function buildTimestamps(
  results: AgentRunResults,
  detail: AgentRunDetail | undefined,
): HookResultsTimestamps {
  const ts = results.timestamps ?? detail?.timestamps ?? {};
  return {
    createdAt: ts.created_at ?? null,
    startedAt: ts.started_at ?? null,
    completedAt: ts.completed_at ?? null,
    updatedAt: ts.updated_at ?? null,
  };
}

export function normalizeHookResults(
  agentRunId: string,
  results: AgentRunResults | undefined,
  detail: AgentRunDetail | undefined,
): HookResultsViewModel | null {
  if (!results) return null;

  const status = results.status;
  const variants = rankHookVariants(collectVariants(results));
  const dimensions = collectDimensions(results);
  const suggestions = collectSuggestions(results);

  const overallFromScores = safeNumber(results.scores?.overall);
  const overallFromPrimary = safeNumber(results.outputs[0]?.primary?.overall);
  const overallScore =
    overallFromScores ?? overallFromPrimary ?? (variants[0]?.overall ?? null);

  const hasDisplayableResults =
    variants.length > 0 || dimensions.length > 0 || suggestions.length > 0;

  return {
    agentRunId,
    status,
    isPolling: isInFlightWorkflowStatus(status),
    overallScore,
    variants,
    dimensions,
    suggestions,
    metadata: buildMetadata(results, detail),
    timestamps: buildTimestamps(results, detail),
    errorMessage: results.error ? formatWorkflowError(results.error) : null,
    hasDisplayableResults,
  };
}

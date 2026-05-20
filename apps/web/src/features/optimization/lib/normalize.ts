import { asArray, asRecord } from '@/shared/api/normalize';
import type {
  OptimizationLoopDto,
  OptimizationRecommendationDto,
  OptimizationSnapshotDto,
} from '@/features/optimization/types/dashboard';

export function normalizeLoop(raw: Record<string, unknown>): OptimizationLoopDto {
  return {
    id: str(raw.id),
    workspace_id: str(raw.workspace_id),
    loop_type: str(raw.loop_type),
    status: str(raw.status),
    correlation_key: str(raw.correlation_key),
    current_cycle: num(raw.current_cycle),
    config: asRecord(raw.config),
    ml_state: asRecord(raw.ml_state),
    metadata: asRecord(raw.metadata),
    started_at: str(raw.started_at),
    last_run_at: raw.last_run_at != null ? str(raw.last_run_at) : null,
    completed_at: raw.completed_at != null ? str(raw.completed_at) : null,
  };
}

export function normalizeSnapshot(raw: Record<string, unknown>): OptimizationSnapshotDto {
  return {
    id: str(raw.id),
    workspace_id: str(raw.workspace_id),
    optimization_loop_id: str(raw.optimization_loop_id),
    cycle_number: num(raw.cycle_number),
    status: str(raw.status),
    engine: str(raw.engine),
    focus: str(raw.focus),
    score: num(raw.score),
    confidence: raw.confidence != null ? Number(raw.confidence) : null,
    title: str(raw.title),
    summary: str(raw.summary),
    rationale: raw.rationale != null ? str(raw.rationale) : null,
    baseline_metrics: asRecord(raw.baseline_metrics),
    observed_metrics: asRecord(raw.observed_metrics),
    delta_metrics: asRecord(raw.delta_metrics),
    evidence: asRecord(raw.evidence),
    action_payload: asRecord(raw.action_payload),
    personalization_context: asRecord(raw.personalization_context),
    ml_features: asRecord(raw.ml_features),
    captured_at: str(raw.captured_at),
  };
}

export function normalizeOptimizationRecommendation(
  raw: Record<string, unknown>,
): OptimizationRecommendationDto {
  const ctx = asRecord(raw.personalization_context);

  return {
    id: str(raw.id),
    type: str(raw.type),
    source: str(raw.source),
    title: str(raw.title),
    summary: str(raw.summary),
    score: num(raw.score),
    confidence: raw.confidence != null ? Number(raw.confidence) : null,
    generated_at: str(raw.generated_at),
    evidence: asRecord(raw.evidence),
    personalization_context: ctx,
    action_payload: asRecord(raw.action_payload),
    cycle_number: ctx.cycle_number != null ? num(ctx.cycle_number) : null,
    optimization_loop_id:
      ctx.optimization_loop_id != null ? str(ctx.optimization_loop_id) : null,
    optimization_snapshot_id:
      ctx.optimization_snapshot_id != null ? str(ctx.optimization_snapshot_id) : null,
  };
}

export function normalizeLoopsPayload(payload: unknown): OptimizationLoopDto[] {
  const data = asRecord(payload);
  return asArray<Record<string, unknown>>(data.loops).map(normalizeLoop);
}

export function normalizeSnapshotsPayload(payload: unknown): OptimizationSnapshotDto[] {
  const data = asRecord(payload);
  return asArray<Record<string, unknown>>(data.snapshots).map(normalizeSnapshot);
}

function str(v: unknown): string {
  return typeof v === 'string' ? v : '';
}

function num(v: unknown): number {
  return typeof v === 'number' ? v : Number(v) || 0;
}

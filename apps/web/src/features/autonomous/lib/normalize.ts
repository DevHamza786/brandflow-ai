import { asArray, asRecord } from '@/shared/api/normalize';
import type { AutonomousSnapshotDto, AutonomousWorkflowDto } from '@/features/autonomous/types/dashboard';

export function normalizeWorkflow(raw: Record<string, unknown>): AutonomousWorkflowDto {
  return {
    id: str(raw.id),
    workspace_id: str(raw.workspace_id),
    status: str(raw.status),
    mode: (str(raw.mode) || 'suggest') as AutonomousWorkflowDto['mode'],
    correlation_key: str(raw.correlation_key),
    current_cycle: num(raw.current_cycle),
    config: asRecord(raw.config),
    ml_state: asRecord(raw.ml_state),
    manual_override_enabled: Boolean(raw.manual_override_enabled),
    autonomous_execution_enabled: Boolean(raw.autonomous_execution_enabled),
    last_run_at: raw.last_run_at != null ? str(raw.last_run_at) : null,
  };
}

export function normalizeSnapshot(raw: Record<string, unknown>): AutonomousSnapshotDto {
  return {
    id: str(raw.id),
    workspace_id: str(raw.workspace_id),
    autonomous_workflow_id: str(raw.autonomous_workflow_id),
    cycle_number: num(raw.cycle_number),
    status: str(raw.status),
    decision_type: str(raw.decision_type),
    engine: str(raw.engine),
    focus: str(raw.focus),
    score: num(raw.score),
    confidence: raw.confidence != null ? Number(raw.confidence) : null,
    title: str(raw.title),
    summary: str(raw.summary),
    blocked_reason: raw.blocked_reason != null ? str(raw.blocked_reason) : null,
    decision_payload: asRecord(raw.decision_payload),
    evidence: asRecord(raw.evidence),
    action_payload: asRecord(raw.action_payload),
    captured_at: str(raw.captured_at),
  };
}

function str(v: unknown): string {
  return typeof v === 'string' ? v : '';
}

function num(v: unknown): number {
  return typeof v === 'number' ? v : Number(v) || 0;
}

export function normalizeWorkflowsPayload(payload: unknown): AutonomousWorkflowDto[] {
  const data = asRecord(payload);
  return asArray<Record<string, unknown>>(data.workflows).map(normalizeWorkflow);
}

export function normalizeSnapshotsPayload(payload: unknown): AutonomousSnapshotDto[] {
  const data = asRecord(payload);
  return asArray<Record<string, unknown>>(data.snapshots).map(normalizeSnapshot);
}

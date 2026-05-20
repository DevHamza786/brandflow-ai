import { apiGet, apiPatch, apiPost } from '@/shared/api/client';
import { asRecord } from '@/shared/api/normalize';
import {
  normalizeSnapshot,
  normalizeWorkflow,
  normalizeSnapshotsPayload,
  normalizeWorkflowsPayload,
} from '@/features/autonomous/lib/normalize';
import type {
  AutonomousSnapshotDto,
  AutonomousWorkflowDto,
  RunAutonomousExecutionResponse,
} from '@/features/autonomous/types/dashboard';

export async function fetchAutonomousWorkflows(): Promise<AutonomousWorkflowDto[]> {
  const data = await apiGet<unknown>('/autonomous/workflows');
  return normalizeWorkflowsPayload(data);
}

export async function fetchAutonomousSnapshots(workflowId?: string): Promise<AutonomousSnapshotDto[]> {
  const qs = workflowId ? `?workflow_id=${encodeURIComponent(workflowId)}` : '';
  const data = await apiGet<unknown>(`/autonomous/snapshots${qs}`);
  return normalizeSnapshotsPayload(data);
}

export async function runAutonomousExecution(): Promise<RunAutonomousExecutionResponse> {
  const data = await apiPost<Record<string, unknown>>('/autonomous/executions/run', {});
  return {
    workflow: normalizeWorkflow(asRecord(data.workflow)),
    cycle_number: Number(data.cycle_number) || 0,
    snapshots_created: Number(data.snapshots_created) || 0,
    blocked_count: Number(data.blocked_count) || 0,
    approved_count: Number(data.approved_count) || 0,
    counts_by_status: asRecord(data.counts_by_status) as Record<string, number>,
    snapshots: (Array.isArray(data.snapshots) ? data.snapshots : []).map((s) =>
      normalizeSnapshot(s as Record<string, unknown>),
    ),
  };
}

export async function updateAutonomousWorkflow(
  workflowId: string,
  body: Record<string, unknown>,
): Promise<AutonomousWorkflowDto> {
  const data = await apiPatch<Record<string, unknown>>(`/autonomous/workflows/${workflowId}`, body);
  return normalizeWorkflow(asRecord(data));
}

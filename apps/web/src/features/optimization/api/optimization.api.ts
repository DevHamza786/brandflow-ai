import { apiGet, apiPost } from '@/shared/api/client';
import { asArray, asRecord } from '@/shared/api/normalize';
import {
  normalizeLoop,
  normalizeOptimizationRecommendation,
  normalizeSnapshot,
  normalizeLoopsPayload,
  normalizeSnapshotsPayload,
} from '@/features/optimization/lib/normalize';
import type {
  OptimizationLoopDto,
  OptimizationRecommendationDto,
  OptimizationSnapshotDto,
  RunOptimizationCycleResponse,
} from '@/features/optimization/types/dashboard';

export async function fetchOptimizationLoops(): Promise<OptimizationLoopDto[]> {
  const data = await apiGet<unknown>('/optimization/loops');
  return normalizeLoopsPayload(data);
}

export async function fetchOptimizationSnapshots(
  loopId?: string,
): Promise<OptimizationSnapshotDto[]> {
  const qs = loopId ? `?loop_id=${encodeURIComponent(loopId)}` : '';
  const data = await apiGet<unknown>(`/optimization/snapshots${qs}`);
  return normalizeSnapshotsPayload(data);
}

export async function fetchOptimizationRecommendations(): Promise<
  OptimizationRecommendationDto[]
> {
  const data = await apiGet<{ recommendations: Record<string, unknown>[] }>('/recommendations');
  const rows = data.recommendations ?? [];

  return rows
    .filter((r) => str(r.source) === 'optimization_loop')
    .map((r) => normalizeOptimizationRecommendation(r));
}

export async function runOptimizationCycle(body: {
  lookback_days?: number;
  comparison_days?: number;
}): Promise<RunOptimizationCycleResponse> {
  const data = await apiPost<Record<string, unknown>>('/optimization/cycles/run', body);

  return {
    loop: normalizeLoop(asRecord(data.loop)),
    cycle_number: num(data.cycle_number),
    snapshots_created: num(data.snapshots_created),
    recommendations_synced: num(data.recommendations_synced),
    counts_by_engine: asRecord(data.counts_by_engine) as Record<string, number>,
    snapshots: asArray<Record<string, unknown>>(data.snapshots).map(normalizeSnapshot),
  };
}

function str(v: unknown): string {
  return typeof v === 'string' ? v : '';
}

function num(v: unknown): number {
  return typeof v === 'number' ? v : Number(v) || 0;
}

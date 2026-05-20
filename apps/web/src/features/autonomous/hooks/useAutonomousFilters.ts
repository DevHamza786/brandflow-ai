import { useCallback, useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import type { AutonomousEngineFilter, AutonomousFilterState } from '@/features/autonomous/types/dashboard';

const ENGINES: AutonomousEngineFilter[] = [
  'all',
  'posting_time_decision',
  'content_selection',
  'posting_decision',
];

export function useAutonomousFilters() {
  const [params, setParams] = useSearchParams();

  const filters = useMemo((): AutonomousFilterState => {
    const engineParam = params.get('engine') ?? 'all';
    const engine = ENGINES.includes(engineParam as AutonomousEngineFilter)
      ? (engineParam as AutonomousEngineFilter)
      : 'all';
    const statusParam = params.get('status') ?? 'all';

    return {
      engine,
      cycleFrom: params.get('cycle_from') ? Number(params.get('cycle_from')) : null,
      cycleTo: params.get('cycle_to') ? Number(params.get('cycle_to')) : null,
      statusFilter:
        statusParam === 'blocked' || statusParam === 'approved' || statusParam === 'proposed'
          ? statusParam
          : 'all',
    };
  }, [params]);

  const patch = useCallback(
    (next: Record<string, string | null>) => {
      setParams((prev) => {
        const copy = new URLSearchParams(prev);
        for (const [k, v] of Object.entries(next)) {
          if (v == null || v === '') {
            copy.delete(k);
          } else {
            copy.set(k, v);
          }
        }
        return copy;
      }, { replace: true });
    },
    [setParams],
  );

  return {
    filters,
    setEngine: (engine: AutonomousEngineFilter) =>
      patch({ engine: engine === 'all' ? null : engine }),
    setStatus: (status: AutonomousFilterState['statusFilter']) =>
      patch({ status: status === 'all' ? null : status }),
    setCycleRange: (from: number | null, to: number | null) =>
      patch({
        cycle_from: from != null ? String(from) : null,
        cycle_to: to != null ? String(to) : null,
      }),
  };
}

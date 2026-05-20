import { useCallback, useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import type {
  OptimizationEngineId,
  OptimizationFilterState,
} from '@/features/optimization/types/dashboard';

const ENGINES: OptimizationEngineId[] = [
  'all',
  'hook_structure',
  'posting_time',
  'cta',
  'audience_fit',
];

export function useOptimizationFilters(): {
  filters: OptimizationFilterState;
  setEngine: (engine: OptimizationEngineId) => void;
  setCycleRange: (from: number | null, to: number | null) => void;
  setLookback: (days: 30 | 60 | 90) => void;
  setComparison: (days: 30 | 60 | 90) => void;
} {
  const [params, setParams] = useSearchParams();

  const filters = useMemo((): OptimizationFilterState => {
    const engineParam = params.get('engine') ?? 'all';
    const engine = ENGINES.includes(engineParam as OptimizationEngineId)
      ? (engineParam as OptimizationEngineId)
      : 'all';

    const lookback = parseDays(params.get('lookback'), 30);
    const comparison = parseDays(params.get('comparison'), 30);

    const cycleFrom = params.get('cycle_from');
    const cycleTo = params.get('cycle_to');

    return {
      engine,
      cycleFrom: cycleFrom != null ? Number(cycleFrom) : null,
      cycleTo: cycleTo != null ? Number(cycleTo) : null,
      lookbackDays: lookback,
      comparisonDays: comparison,
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
    setEngine: (engine) => patch({ engine: engine === 'all' ? null : engine }),
    setCycleRange: (from, to) =>
      patch({
        cycle_from: from != null ? String(from) : null,
        cycle_to: to != null ? String(to) : null,
      }),
    setLookback: (days) => patch({ lookback: String(days) }),
    setComparison: (days) => patch({ comparison: String(days) }),
  };
}

function parseDays(raw: string | null, fallback: 30 | 60 | 90): 30 | 60 | 90 {
  const n = Number(raw);
  if (n === 60 || n === 90) {
    return n;
  }
  return fallback;
}

import type {
  OptimizationEngineId,
  OptimizationFilterState,
} from '@/features/optimization/types/dashboard';
import { cn } from '@/shared/lib/cn';

const ENGINES: { id: OptimizationEngineId; label: string }[] = [
  { id: 'all', label: 'All engines' },
  { id: 'hook_structure', label: 'Hooks' },
  { id: 'posting_time', label: 'Timing' },
  { id: 'cta', label: 'CTA' },
  { id: 'audience_fit', label: 'Audience' },
];

const PERIODS: { days: 30 | 60 | 90; label: string }[] = [
  { days: 30, label: '30d' },
  { days: 60, label: '60d' },
  { days: 90, label: '90d' },
];

type Props = {
  filters: OptimizationFilterState;
  maxCycle: number;
  onEngine: (engine: OptimizationEngineId) => void;
  onLookback: (days: 30 | 60 | 90) => void;
  onComparison: (days: 30 | 60 | 90) => void;
  onCycleRange: (from: number | null, to: number | null) => void;
};

export function OptimizationFilters({
  filters,
  maxCycle,
  onEngine,
  onLookback,
  onComparison,
  onCycleRange,
}: Props) {
  return (
    <div className="flex flex-col gap-4 rounded-xl border border-border/80 bg-surface-raised/50 p-4 sm:flex-row sm:flex-wrap sm:items-end">
      <div className="min-w-[10rem] flex-1">
        <p className="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">Engine</p>
        <div className="flex flex-wrap gap-1.5">
          {ENGINES.map((e) => (
            <button
              key={e.id}
              type="button"
              onClick={() => onEngine(e.id)}
              className={cn(
                'rounded-lg px-3 py-1.5 text-xs font-medium transition-colors',
                filters.engine === e.id
                  ? 'bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-500/40'
                  : 'bg-surface-overlay text-slate-400 hover:text-slate-200',
              )}
            >
              {e.label}
            </button>
          ))}
        </div>
      </div>

      <div>
        <p className="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">
          Lookback (run)
        </p>
        <div className="flex gap-1.5">
          {PERIODS.map((p) => (
            <button
              key={`lb-${p.days}`}
              type="button"
              onClick={() => onLookback(p.days)}
              className={cn(
                'rounded-lg px-3 py-1.5 text-xs font-medium',
                filters.lookbackDays === p.days
                  ? 'bg-accent/20 text-accent'
                  : 'text-slate-500 hover:bg-surface-overlay',
              )}
            >
              {p.label}
            </button>
          ))}
        </div>
      </div>

      <div>
        <p className="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">
          Compare prior
        </p>
        <div className="flex gap-1.5">
          {PERIODS.map((p) => (
            <button
              key={`cmp-${p.days}`}
              type="button"
              onClick={() => onComparison(p.days)}
              className={cn(
                'rounded-lg px-3 py-1.5 text-xs font-medium',
                filters.comparisonDays === p.days
                  ? 'bg-accent/20 text-accent'
                  : 'text-slate-500 hover:bg-surface-overlay',
              )}
            >
              {p.label}
            </button>
          ))}
        </div>
      </div>

      {maxCycle > 0 && (
        <div>
          <p className="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">
            Cycle range
          </p>
          <div className="flex items-center gap-2">
            <select
              className="rounded-lg border border-border bg-surface-overlay px-2 py-1.5 text-xs text-slate-200"
              value={filters.cycleFrom ?? ''}
              onChange={(e) => {
                const from = e.target.value ? Number(e.target.value) : null;
                onCycleRange(from, filters.cycleTo);
              }}
            >
              <option value="">From</option>
              {Array.from({ length: maxCycle }, (_, i) => i + 1).map((c) => (
                <option key={c} value={c}>
                  Cycle {c}
                </option>
              ))}
            </select>
            <span className="text-slate-600">→</span>
            <select
              className="rounded-lg border border-border bg-surface-overlay px-2 py-1.5 text-xs text-slate-200"
              value={filters.cycleTo ?? ''}
              onChange={(e) => {
                const to = e.target.value ? Number(e.target.value) : null;
                onCycleRange(filters.cycleFrom, to);
              }}
            >
              <option value="">To</option>
              {Array.from({ length: maxCycle }, (_, i) => i + 1).map((c) => (
                <option key={c} value={c}>
                  Cycle {c}
                </option>
              ))}
            </select>
          </div>
        </div>
      )}
    </div>
  );
}

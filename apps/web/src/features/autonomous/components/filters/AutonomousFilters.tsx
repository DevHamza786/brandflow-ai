import type { AutonomousEngineFilter, AutonomousFilterState } from '@/features/autonomous/types/dashboard';
import { cn } from '@/shared/lib/cn';

type Props = {
  filters: AutonomousFilterState;
  maxCycle: number;
  onEngine: (e: AutonomousEngineFilter) => void;
  onStatus: (s: AutonomousFilterState['statusFilter']) => void;
  onCycleRange: (from: number | null, to: number | null) => void;
};

export function AutonomousFilters({ filters, maxCycle, onEngine, onStatus, onCycleRange }: Props) {
  const engines: { id: AutonomousEngineFilter; label: string }[] = [
    { id: 'all', label: 'All' },
    { id: 'posting_time_decision', label: 'Timing' },
    { id: 'content_selection', label: 'Content' },
    { id: 'posting_decision', label: 'Publish' },
  ];

  return (
    <div className="flex flex-col gap-4 rounded-xl border border-border/80 bg-surface-raised/50 p-4">
      <div>
        <p className="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">Engine</p>
        <div className="flex flex-wrap gap-1.5">
          {engines.map((e) => (
            <button
              key={e.id}
              type="button"
              onClick={() => onEngine(e.id)}
              className={cn(
                'rounded-lg px-3 py-1.5 text-xs font-medium',
                filters.engine === e.id
                  ? 'bg-sky-500/20 text-sky-300 ring-1 ring-sky-500/40'
                  : 'bg-surface-overlay text-slate-500',
              )}
            >
              {e.label}
            </button>
          ))}
        </div>
      </div>
      <div>
        <p className="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">Outcome</p>
        <div className="flex flex-wrap gap-1.5">
          {(['all', 'proposed', 'approved', 'blocked'] as const).map((s) => (
            <button
              key={s}
              type="button"
              onClick={() => onStatus(s)}
              className={cn(
                'rounded-lg px-3 py-1.5 text-xs font-medium capitalize',
                filters.statusFilter === s
                  ? 'bg-accent/20 text-accent'
                  : 'text-slate-500 hover:bg-surface-overlay',
              )}
            >
              {s}
            </button>
          ))}
        </div>
      </div>
      {maxCycle > 0 && (
        <div className="flex gap-2">
          <select
            className="rounded-lg border border-border bg-surface-overlay px-2 py-1.5 text-xs text-slate-200"
            value={filters.cycleFrom ?? ''}
            onChange={(e) =>
              onCycleRange(e.target.value ? Number(e.target.value) : null, filters.cycleTo)
            }
          >
            <option value="">From cycle</option>
            {Array.from({ length: maxCycle }, (_, i) => i + 1).map((c) => (
              <option key={c} value={c}>
                {c}
              </option>
            ))}
          </select>
          <select
            className="rounded-lg border border-border bg-surface-overlay px-2 py-1.5 text-xs text-slate-200"
            value={filters.cycleTo ?? ''}
            onChange={(e) =>
              onCycleRange(filters.cycleFrom, e.target.value ? Number(e.target.value) : null)
            }
          >
            <option value="">To cycle</option>
            {Array.from({ length: maxCycle }, (_, i) => i + 1).map((c) => (
              <option key={c} value={c}>
                {c}
              </option>
            ))}
          </select>
        </div>
      )}
    </div>
  );
}

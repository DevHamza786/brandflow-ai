import { useState } from 'react';
import type { AnalyticsDatePreset, DashboardFilterState } from '@/features/analytics/types/dashboard';
import { defaultCustomEnd } from '@/features/analytics/hooks/useDashboardFilters';
import { cn } from '@/shared/lib/cn';
import { Button } from '@/shared/components/ui/Button';
import { Input } from '@/shared/components/ui/Input';

const PRESETS: { id: AnalyticsDatePreset; label: string }[] = [
  { id: '7d', label: '7 days' },
  { id: '30d', label: '30 days' },
  { id: '90d', label: '90 days' },
];

type Props = {
  filters: DashboardFilterState;
  rangeLabel?: string;
  onPreset: (preset: AnalyticsDatePreset) => void;
  onCustomRange: (from: string, to: string) => void;
};

export function DashboardFilters({ filters, rangeLabel, onPreset, onCustomRange }: Props) {
  const [customFrom, setCustomFrom] = useState(filters.from ?? '');
  const [customTo, setCustomTo] = useState(filters.to ?? defaultCustomEnd());
  const [showCustom, setShowCustom] = useState(filters.preset === 'custom');

  return (
    <div className="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
      <div className="flex flex-wrap gap-2">
        {PRESETS.map((p) => (
          <button
            key={p.id}
            type="button"
            onClick={() => {
              setShowCustom(false);
              onPreset(p.id);
            }}
            className={cn(
              'rounded-lg border px-3 py-1.5 text-sm font-medium transition-all',
              filters.preset === p.id && !showCustom
                ? 'border-accent/50 bg-accent/15 text-accent shadow-[0_0_20px_-8px_rgba(59,158,255,0.5)]'
                : 'border-border/80 bg-surface-overlay/50 text-slate-400 hover:border-border hover:text-slate-200',
            )}
          >
            {p.label}
          </button>
        ))}
        <button
          type="button"
          onClick={() => setShowCustom((v) => !v)}
          className={cn(
            'rounded-lg border px-3 py-1.5 text-sm font-medium transition-all',
            showCustom || filters.preset === 'custom'
              ? 'border-accent/50 bg-accent/15 text-accent'
              : 'border-border/80 bg-surface-overlay/50 text-slate-400 hover:text-slate-200',
          )}
        >
          Custom
        </button>
      </div>

      {rangeLabel && (
        <p className="text-xs text-slate-500">
          Showing <span className="font-medium text-slate-300">{rangeLabel}</span>
        </p>
      )}

      {showCustom && (
        <form
          className="flex w-full flex-wrap items-end gap-3 sm:w-auto"
          onSubmit={(e) => {
            e.preventDefault();
            onCustomRange(customFrom, customTo);
          }}
        >
          <label className="flex flex-col gap-1 text-xs text-slate-500">
            From
            <Input type="date" value={customFrom} onChange={(e) => setCustomFrom(e.target.value)} />
          </label>
          <label className="flex flex-col gap-1 text-xs text-slate-500">
            To
            <Input type="date" value={customTo} onChange={(e) => setCustomTo(e.target.value)} />
          </label>
          <Button type="submit" variant="secondary" className="shrink-0">
            Apply
          </Button>
        </form>
      )}
    </div>
  );
}

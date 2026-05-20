import type { CompetitorSummary } from '@/features/competitors/types/dashboard';
import { cn } from '@/shared/lib/cn';

type Props = {
  competitors: CompetitorSummary[];
  selectedId: string | null;
  onSelect: (id: string) => void;
};

export function CompetitorSelector({ competitors, selectedId, onSelect }: Props) {
  if (competitors.length === 0) {
    return null;
  }

  return (
    <div className="flex flex-wrap gap-2">
      {competitors.map((c) => (
        <button
          key={c.id}
          type="button"
          onClick={() => onSelect(c.id)}
          className={cn(
            'rounded-lg border px-3 py-2 text-left text-sm transition-all',
            selectedId === c.id
              ? 'border-accent/50 bg-accent/15 text-accent shadow-[0_0_20px_-8px_rgba(59,158,255,0.45)]'
              : 'border-border/80 bg-surface-overlay/40 text-slate-400 hover:border-border hover:text-slate-200',
          )}
        >
          <span className="font-medium">{c.name ?? 'Unnamed competitor'}</span>
          {c.labels.length > 0 && (
            <span className="mt-0.5 block text-xs opacity-80">{c.labels.join(' · ')}</span>
          )}
        </button>
      ))}
    </div>
  );
}

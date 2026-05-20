import { Skeleton } from '@/shared/components/ui/Skeleton';
import { cn } from '@/shared/lib/cn';

type Props = {
  label: string;
  value: string;
  delta?: string | null;
  deltaPositive?: boolean | null;
  hint?: string;
  loading?: boolean;
  className?: string;
};

export function KpiCard({
  label,
  value,
  delta,
  deltaPositive,
  hint,
  loading,
  className,
}: Props) {
  if (loading) {
    return (
      <div className={cn('rounded-xl border border-border/80 bg-surface-raised/80 p-4', className)}>
        <Skeleton className="h-3 w-24" />
        <Skeleton className="mt-3 h-8 w-20" />
        <Skeleton className="mt-2 h-3 w-16" />
      </div>
    );
  }

  return (
    <div
      className={cn(
        'group rounded-xl border border-border/80 bg-surface-raised/80 p-4 transition-all hover:border-accent/30 hover:shadow-glow/20',
        className,
      )}
    >
      <p className="text-xs font-medium uppercase tracking-wider text-slate-500">{label}</p>
      <p className="mt-2 font-mono text-2xl font-semibold tracking-tight text-white">{value}</p>
      {(delta != null || hint) && (
        <div className="mt-2 flex flex-wrap items-center gap-2 text-xs">
          {delta != null && (
            <span
              className={cn(
                'font-medium',
                deltaPositive === true && 'text-emerald-400',
                deltaPositive === false && 'text-rose-400',
                deltaPositive == null && 'text-slate-500',
              )}
            >
              {delta} vs prior period
            </span>
          )}
          {hint && <span className="text-slate-500">{hint}</span>}
        </div>
      )}
    </div>
  );
}

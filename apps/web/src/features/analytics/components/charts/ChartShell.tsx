import type { ReactNode } from 'react';
import { cn } from '@/shared/lib/cn';

type Props = {
  title: string;
  subtitle?: string;
  children: ReactNode;
  className?: string;
  heightClass?: string;
  aiHint?: string;
};

export function ChartShell({
  title,
  subtitle,
  children,
  className,
  heightClass = 'h-[min(320px,42vw)] min-h-[220px]',
  aiHint,
}: Props) {
  return (
    <div
      className={cn(
        'flex flex-col rounded-xl border border-border/80 bg-surface-raised/60 transition-shadow hover:shadow-glow/30',
        className,
      )}
    >
      <div className="flex flex-wrap items-start justify-between gap-2 border-b border-border/60 px-4 py-3 sm:px-5">
        <div>
          <h3 className="text-sm font-medium text-slate-200">{title}</h3>
          {subtitle && <p className="mt-0.5 text-xs text-slate-500">{subtitle}</p>}
        </div>
        {aiHint && (
          <span className="rounded-md bg-accent/10 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider text-accent">
            {aiHint}
          </span>
        )}
      </div>
      <div className={cn('w-full px-2 pb-2 pt-1 sm:px-3', heightClass)}>{children}</div>
    </div>
  );
}

import type { ScoreDimensionRow } from '@/features/results/types/results.types';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { cn } from '@/shared/lib/cn';

type Props = {
  dimensions: ScoreDimensionRow[];
  className?: string;
};

export function ScoreBreakdown({ dimensions, className }: Props) {
  if (dimensions.length === 0) return null;

  const max = Math.max(...dimensions.map((d) => d.value), 100);

  return (
    <Card className={cn('transition-opacity duration-300', className)}>
      <CardHeader>
        <h3 className="text-sm font-semibold text-slate-200">Score breakdown</h3>
        <p className="mt-1 text-xs text-slate-500">
          Dimension scores from the hook evaluation rubric
        </p>
      </CardHeader>
      <CardBody className="space-y-4">
        {dimensions.map((row) => {
          const pct = Math.min(100, Math.round((row.value / max) * 100));
          return (
            <div key={row.key} className="space-y-1.5">
              <div className="flex items-center justify-between gap-2 text-sm">
                <span className="text-slate-300">{row.label}</span>
                <span className="font-mono text-slate-400">{row.value.toFixed(0)}</span>
              </div>
              <div className="h-2 overflow-hidden rounded-full bg-surface-overlay">
                <div
                  className="h-full rounded-full bg-gradient-to-r from-accent-muted to-accent transition-all duration-700 ease-out"
                  style={{ width: `${pct}%` }}
                />
              </div>
            </div>
          );
        })}
      </CardBody>
    </Card>
  );
}

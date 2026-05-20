import type { OptimizationSnapshotDto } from '@/features/optimization/types/dashboard';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { shortDate } from '@/features/analytics/lib/format';
import { EmptyState } from '@/shared/components/feedback/EmptyState';

type Props = {
  timeline: OptimizationSnapshotDto[];
};

export function OptimizationHistoryTimeline({ timeline }: Props) {
  const rows = timeline.slice(0, 12);

  return (
    <Card>
      <CardHeader>
        <h2 className="text-sm font-medium text-slate-200">Optimization history</h2>
        <p className="mt-1 text-xs text-slate-500">Recent intelligence snapshots across cycles</p>
      </CardHeader>
      <CardBody>
        {rows.length === 0 ? (
          <EmptyState
            title="No history yet"
            description="Each optimization cycle appends scored snapshots you can audit here."
          />
        ) : (
          <ol className="relative border-l border-border/80 pl-4">
            {rows.map((s) => (
              <li key={s.id} className="mb-6 ml-2 last:mb-0">
                <span className="absolute -left-[5px] mt-1.5 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-4 ring-surface" />
                <time className="text-xs text-slate-500">
                  {shortDate(s.captured_at)} · Cycle {s.cycle_number}
                </time>
                <p className="mt-1 text-sm font-medium text-slate-200">{s.title}</p>
                <p className="mt-0.5 text-xs text-emerald-400/80">
                  {s.engine.replace(/_/g, ' ')} · Score {s.score}
                  {typeof s.delta_metrics.uplift_pct === 'number' &&
                    ` · +${Number(s.delta_metrics.uplift_pct).toFixed(1)}%`}
                </p>
                <p className="mt-2 line-clamp-2 text-sm text-slate-400">{s.summary}</p>
              </li>
            ))}
          </ol>
        )}
      </CardBody>
    </Card>
  );
}

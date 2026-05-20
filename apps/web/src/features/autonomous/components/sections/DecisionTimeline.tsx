import type { AutonomousSnapshotDto } from '@/features/autonomous/types/dashboard';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { shortDate } from '@/features/analytics/lib/format';
import { EmptyState } from '@/shared/components/feedback/EmptyState';

type Props = { timeline: AutonomousSnapshotDto[] };

export function DecisionTimeline({ timeline }: Props) {
  const rows = timeline.slice(0, 14);

  return (
    <Card>
      <CardHeader>
        <h2 className="text-sm font-medium text-slate-200">Autonomous decision timeline</h2>
      </CardHeader>
      <CardBody>
        {rows.length === 0 ? (
          <EmptyState title="No decisions" description="Run an evaluation cycle to populate the timeline." />
        ) : (
          <ol className="relative border-l border-border/80 pl-4">
            {rows.map((s) => (
              <li key={s.id} className="mb-5 ml-2">
                <span
                  className={`absolute -left-[5px] mt-1.5 h-2.5 w-2.5 rounded-full ring-4 ring-surface ${
                    s.status.startsWith('blocked_') ? 'bg-rose-400' : 'bg-sky-400'
                  }`}
                />
                <time className="text-xs text-slate-500">
                  {shortDate(s.captured_at)} · C{s.cycle_number}
                </time>
                <p className="text-sm font-medium text-slate-200">{s.title}</p>
                <p className="text-xs text-sky-400/90">
                  {s.status.replace(/_/g, ' ')}
                  {s.confidence != null && ` · ${(s.confidence * 100).toFixed(0)}% conf.`}
                </p>
                <p className="mt-1 line-clamp-2 text-sm text-slate-400">{s.summary}</p>
              </li>
            ))}
          </ol>
        )}
      </CardBody>
    </Card>
  );
}

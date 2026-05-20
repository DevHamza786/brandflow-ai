import type { AutonomousSnapshotDto } from '@/features/autonomous/types/dashboard';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { Badge } from '@/shared/components/ui/Badge';

type Props = { timeline: AutonomousSnapshotDto[] };

export function ExecutionPanels({ timeline }: Props) {
  const latest = timeline.slice(0, 4);

  return (
    <Card className="border-sky-500/20 bg-sky-500/5">
      <CardHeader>
        <div className="flex items-center gap-2">
          <h2 className="text-sm font-medium text-slate-200">Execution panels</h2>
          <Badge className="border-sky-500/40 bg-sky-500/15 text-sky-300">Infrastructure only</Badge>
        </div>
        <p className="mt-1 text-xs text-slate-500">
          Decisions are persisted with action_payload — Schedule domain publishes when execution is enabled.
        </p>
      </CardHeader>
      <CardBody className="grid gap-3 sm:grid-cols-2">
        {latest.length === 0 ? (
          <p className="text-sm text-slate-500">No execution snapshots.</p>
        ) : (
          latest.map((s) => (
            <div key={s.id} className="rounded-lg border border-border/80 bg-surface-raised/80 p-3">
              <p className="text-xs font-medium uppercase text-slate-500">{s.engine.replace(/_/g, ' ')}</p>
              <p className="mt-1 text-sm text-slate-200">{s.title}</p>
              <pre className="mt-2 max-h-24 overflow-auto rounded bg-surface-overlay/80 p-2 text-[10px] text-slate-400">
                {JSON.stringify(s.action_payload, null, 2)}
              </pre>
            </div>
          ))
        )}
      </CardBody>
    </Card>
  );
}

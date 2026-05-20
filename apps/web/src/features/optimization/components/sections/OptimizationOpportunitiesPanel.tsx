import type { OptimizationOpportunity } from '@/features/optimization/types/dashboard';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { Badge } from '@/shared/components/ui/Badge';
import { EmptyState } from '@/shared/components/feedback/EmptyState';

type Props = {
  opportunities: OptimizationOpportunity[];
};

export function OptimizationOpportunitiesPanel({ opportunities }: Props) {
  return (
    <Card>
      <CardHeader>
        <div className="flex items-center gap-2">
          <h2 className="text-sm font-medium text-slate-200">Optimization opportunities</h2>
          <Badge className="border-amber-500/40 bg-amber-500/10 text-amber-300">Mid-band</Badge>
        </div>
        <p className="mt-1 text-xs text-slate-500">
          Proposed signals with room to improve — candidates for automated experimentation
        </p>
      </CardHeader>
      <CardBody>
        {opportunities.length === 0 ? (
          <EmptyState
            title="No mid-tier opportunities"
            description="Strong winners and low scores are filtered elsewhere. Run more cycles to surface tuning candidates."
          />
        ) : (
          <ul className="space-y-3">
            {opportunities.map((o) => (
              <li
                key={o.id}
                className="rounded-lg border border-amber-500/20 bg-surface-raised/80 px-4 py-3"
              >
                <div className="flex justify-between gap-2">
                  <p className="text-sm font-medium text-slate-200">{o.title}</p>
                  <span className="shrink-0 font-mono text-xs text-amber-300">{o.score}</span>
                </div>
                <p className="mt-1 text-xs text-slate-500">
                  {o.engine.replace(/_/g, ' ')} · Cycle {o.cycle_number}
                </p>
                <p className="mt-2 text-sm text-slate-400">{o.summary}</p>
              </li>
            ))}
          </ul>
        )}
      </CardBody>
    </Card>
  );
}

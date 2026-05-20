import type { ExperimentCompareRow } from '@/features/optimization/types/dashboard';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { formatDeltaPct } from '@/features/analytics/lib/format';

type Props = {
  rows: ExperimentCompareRow[];
};

export function ExperimentComparisonPanel({ rows }: Props) {
  return (
    <Card className="border-violet-500/20">
      <CardHeader>
        <h2 className="text-sm font-medium text-slate-200">Experiment outcomes</h2>
        <p className="mt-1 text-xs text-slate-500">
          Compare prior vs current cycle per engine — foundation for A/B and bandit policies
        </p>
      </CardHeader>
      <CardBody>
        <div className="overflow-x-auto">
          <table className="w-full min-w-[320px] text-left text-sm">
            <thead>
              <tr className="border-b border-border/80 text-xs uppercase tracking-wider text-slate-500">
                <th className="pb-2 pr-4 font-medium">Engine</th>
                <th className="pb-2 pr-4 font-medium">Cycle {rows[0]?.cycle_a ?? '—'}</th>
                <th className="pb-2 pr-4 font-medium">Cycle {rows[0]?.cycle_b ?? '—'}</th>
                <th className="pb-2 font-medium">Score Δ</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r) => (
                <tr key={r.engine} className="border-b border-border/40 text-slate-300">
                  <td className="py-3 pr-4 font-medium text-slate-200">{r.label}</td>
                  <td className="py-3 pr-4 font-mono text-xs">
                    {r.uplift_a != null ? formatDeltaPct(r.uplift_a) : '—'}
                  </td>
                  <td className="py-3 pr-4 font-mono text-xs text-emerald-300">
                    {r.uplift_b != null ? formatDeltaPct(r.uplift_b) : '—'}
                  </td>
                  <td className="py-3 font-mono text-xs">
                    {r.score_delta != null
                      ? `${r.score_delta > 0 ? '+' : ''}${r.score_delta}`
                      : '—'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </CardBody>
    </Card>
  );
}

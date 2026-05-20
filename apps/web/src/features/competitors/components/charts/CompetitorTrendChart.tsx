import {
  CartesianGrid,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { CompetitorTrends } from '@/features/competitors/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';

type Props = { trends: CompetitorTrends; currentScore: number | null };

export function CompetitorTrendChart({ trends, currentScore }: Props) {
  if (trends.status === 'insufficient_history') {
    return (
      <ChartShell title="Performance trends" subtitle="Snapshot-over-snapshot deltas">
        <div className="flex h-full flex-col items-center justify-center gap-2 px-6 text-center text-sm text-slate-500">
          <p>Ingest another snapshot to unlock trend detection.</p>
          <p className="text-xs">Adaptive intelligence needs at least two observations.</p>
        </div>
      </ChartShell>
    );
  }

  const prevScore =
    currentScore != null && trends.intelligence_score_delta != null
      ? currentScore - trends.intelligence_score_delta
      : null;

  const rows = [
    { label: 'Previous', score: prevScore ?? undefined },
    { label: 'Current', score: currentScore ?? undefined },
  ].filter((r) => r.score != null);

  return (
    <ChartShell title="Intelligence score trend" subtitle="Latest vs prior snapshot" aiHint="Trend">
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={rows} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 12 }} tickLine={false} />
          <YAxis domain={[0, 100]} tick={{ fill: chartColors.axis, fontSize: 11 }} width={36} tickLine={false} />
          <Tooltip content={<ChartTooltip />} />
          <Line
            type="monotone"
            dataKey="score"
            name="Score"
            stroke={chartColors.amber}
            strokeWidth={2}
            dot={{ r: 4, fill: chartColors.amber }}
          />
        </LineChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

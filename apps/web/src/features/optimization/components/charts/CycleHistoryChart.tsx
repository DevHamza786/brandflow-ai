import {
  CartesianGrid,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import type { CycleHistoryPoint } from '@/features/optimization/types/dashboard';

type Props = { data: CycleHistoryPoint[] };

export function CycleHistoryChart({ data }: Props) {
  const byCycle = new Map<number, { cycle: string; avg_score: number; n: number }>();
  for (const row of data) {
    const cur = byCycle.get(row.cycle) ?? { cycle: `C${row.cycle}`, avg_score: 0, n: 0 };
    cur.avg_score += row.score;
    cur.n += 1;
    byCycle.set(row.cycle, cur);
  }
  const chartData = [...byCycle.values()]
    .map((c) => ({ cycle: c.cycle, avg_score: c.n > 0 ? Math.round(c.avg_score / c.n) : 0 }))
    .sort((a, b) => a.cycle.localeCompare(b.cycle));

  if (chartData.length === 0) {
    return (
      <ChartShell title="Cycle history" subtitle="Average snapshot score per cycle" aiHint="History">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          No cycle history yet.
        </div>
      </ChartShell>
    );
  }

  return (
    <ChartShell
      title="Optimization cycle history"
      subtitle="Mean intelligence score across engines per cycle"
      aiHint="History"
    >
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={chartData} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" />
          <XAxis dataKey="cycle" tick={{ fill: chartColors.axis, fontSize: 11 }} />
          <YAxis domain={[0, 100]} tick={{ fill: chartColors.axis, fontSize: 11 }} />
          <Tooltip content={<ChartTooltip />} />
          <Line
            type="monotone"
            dataKey="avg_score"
            name="Avg score"
            stroke={chartColors.accent}
            strokeWidth={2}
            dot={{ r: 4 }}
          />
        </LineChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

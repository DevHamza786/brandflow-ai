import {
  CartesianGrid,
  Legend,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { ScoreTrendPoint } from '@/features/analytics/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import { shortDate } from '@/features/analytics/lib/format';

type Props = { data: ScoreTrendPoint[] };

export function ScoreTrendChart({ data }: Props) {
  const rows = data.map((d) => ({
    ...d,
    label: shortDate(d.date),
    avg_normalized: d.avg_normalized ?? undefined,
    avg_hook_score: d.avg_hook_score ?? undefined,
  }));

  return (
    <ChartShell title="Score trends" subtitle="Daily averages across observed posts" aiHint="Trend">
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={rows} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 11 }} tickLine={false} />
          <YAxis tick={{ fill: chartColors.axis, fontSize: 11 }} tickLine={false} width={36} />
          <Tooltip content={<ChartTooltip />} />
          <Legend wrapperStyle={{ fontSize: 11, color: chartColors.axis }} />
          <Line
            type="monotone"
            dataKey="avg_normalized"
            name="Normalized"
            stroke={chartColors.accent}
            strokeWidth={2}
            dot={false}
            connectNulls
          />
          <Line
            type="monotone"
            dataKey="avg_hook_score"
            name="Hook score"
            stroke={chartColors.amber}
            strokeWidth={2}
            dot={false}
            connectNulls
          />
        </LineChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

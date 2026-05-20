import { Line, LineChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import type { ConfidenceTrendPoint } from '@/features/autonomous/types/dashboard';

type Props = { data: ConfidenceTrendPoint[] };

export function ConfidenceTrendChart({ data }: Props) {
  if (data.length === 0) {
    return (
      <ChartShell title="Confidence trends" aiHint="Safety">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          No confidence data yet.
        </div>
      </ChartShell>
    );
  }

  const chartData = data.map((d) => ({ ...d, cycle: `C${d.cycle}` }));

  return (
    <ChartShell title="Confidence trends" subtitle="Per-decision confidence by cycle (%)" aiHint="Safety">
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={chartData} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" />
          <XAxis dataKey="cycle" tick={{ fill: chartColors.axis, fontSize: 11 }} />
          <YAxis domain={[0, 100]} tick={{ fill: chartColors.axis, fontSize: 11 }} unit="%" />
          <Tooltip content={<ChartTooltip />} />
          <Line type="monotone" dataKey="confidence" stroke={chartColors.accent} strokeWidth={2} dot={{ r: 3 }} />
        </LineChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

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
import type { HookTrendPoint } from '@/features/optimization/types/dashboard';

type Props = { data: HookTrendPoint[] };

export function HookOptimizationTrendChart({ data }: Props) {
  if (data.length === 0) {
    return (
      <ChartShell title="Hook optimization trend" subtitle="Uplift by cycle" aiHint="Hooks">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          No hook-structure snapshots yet.
        </div>
      </ChartShell>
    );
  }

  const chartData = data.map((d) => ({
    cycle: `C${d.cycle}`,
    uplift_pct: d.uplift_pct,
    score: d.score,
    style: d.style_label,
  }));

  return (
    <ChartShell
      title="Hook optimization trend"
      subtitle="Period-over-period hook structure uplift by cycle"
      aiHint="Hooks"
    >
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={chartData} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" />
          <XAxis dataKey="cycle" tick={{ fill: chartColors.axis, fontSize: 11 }} />
          <YAxis tick={{ fill: chartColors.axis, fontSize: 11 }} unit="%" />
          <Tooltip content={<ChartTooltip />} />
          <Line
            type="monotone"
            dataKey="uplift_pct"
            name="Uplift %"
            stroke={chartColors.emerald}
            strokeWidth={2}
            dot={{ r: 4, fill: chartColors.emerald }}
            activeDot={{ r: 6 }}
          />
          <Line
            type="monotone"
            dataKey="score"
            name="Score"
            stroke={chartColors.violet}
            strokeWidth={1.5}
            strokeDasharray="4 4"
            dot={false}
          />
        </LineChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

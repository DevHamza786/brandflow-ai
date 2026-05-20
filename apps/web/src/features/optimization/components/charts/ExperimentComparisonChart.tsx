import {
  Bar,
  BarChart,
  CartesianGrid,
  Legend,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import type { ExperimentCompareRow } from '@/features/optimization/types/dashboard';

type Props = { data: ExperimentCompareRow[] };

export function ExperimentComparisonChart({ data }: Props) {
  const hasData = data.some((r) => r.uplift_a != null || r.uplift_b != null);

  if (!hasData) {
    return (
      <ChartShell
        title="Experiment comparison"
        subtitle="Prior cycle vs current cycle uplift"
        aiHint="Experiments"
      >
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          Run at least two cycles to compare experimentation outcomes.
        </div>
      </ChartShell>
    );
  }

  const cycleA = data[0]?.cycle_a ?? 1;
  const cycleB = data[0]?.cycle_b ?? 2;

  const chartData = data.map((r) => ({
    label: r.label,
    cycle_a: r.uplift_a ?? 0,
    cycle_b: r.uplift_b ?? 0,
  }));

  return (
    <ChartShell
      title="Experiment comparison"
      subtitle={`Cycle ${cycleA} vs ${cycleB} uplift by engine`}
      aiHint="Experiments"
      heightClass="h-[min(360px,48vw)] min-h-[240px]"
    >
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={chartData} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 10 }} />
          <YAxis tick={{ fill: chartColors.axis, fontSize: 11 }} unit="%" />
          <Tooltip content={<ChartTooltip />} />
          <Legend wrapperStyle={{ fontSize: 11, color: chartColors.axis }} />
          <Bar dataKey="cycle_a" name={`Cycle ${cycleA}`} fill={chartColors.slate} radius={[3, 3, 0, 0]} />
          <Bar dataKey="cycle_b" name={`Cycle ${cycleB}`} fill={chartColors.emerald} radius={[3, 3, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

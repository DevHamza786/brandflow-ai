import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import type { PostingTimeOptPoint } from '@/features/optimization/types/dashboard';

type Props = { data: PostingTimeOptPoint[] };

export function PostingTimeOptimizationChart({ data }: Props) {
  if (data.length === 0) {
    return (
      <ChartShell title="Posting-time optimization" subtitle="From latest timing engine" aiHint="Timing">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          Posting-time signals appear after cycles with posted_at data.
        </div>
      </ChartShell>
    );
  }

  const sorted = [...data].sort((a, b) => a.hour - b.hour);

  return (
    <ChartShell
      title="Posting-time optimization"
      subtitle="Hour profile from optimization evidence (UTC)"
      aiHint="Timing"
    >
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={sorted} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 9 }} interval={0} />
          <YAxis tick={{ fill: chartColors.axis, fontSize: 11 }} width={36} />
          <Tooltip content={<ChartTooltip />} />
          <Bar dataKey="avg_normalized" name="Avg normalized" fill={chartColors.emerald} radius={[3, 3, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

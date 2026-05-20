import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import type { PostingDecisionPoint } from '@/features/autonomous/types/dashboard';

type Props = { data: PostingDecisionPoint[] };

export function PostingDecisionChart({ data }: Props) {
  if (data.length === 0) {
    return (
      <ChartShell title="Posting decisions" aiHint="Publish">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">No composite decisions.</div>
      </ChartShell>
    );
  }

  return (
    <ChartShell title="Posting decisions" subtitle="Proceed vs hold by cycle" aiHint="Publish">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={data} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="cycle" tick={{ fill: chartColors.axis, fontSize: 11 }} />
          <YAxis tick={{ fill: chartColors.axis, fontSize: 11 }} />
          <Tooltip content={<ChartTooltip />} />
          <Legend wrapperStyle={{ fontSize: 11, color: chartColors.axis }} />
          <Bar dataKey="should_post" name="Proceed" fill={chartColors.emerald} stackId="a" />
          <Bar dataKey="hold" name="Hold" fill={chartColors.slate} stackId="a" />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

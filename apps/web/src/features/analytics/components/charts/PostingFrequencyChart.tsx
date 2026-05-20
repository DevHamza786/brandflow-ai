import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { PostingFrequencyPoint } from '@/features/analytics/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import { shortDate } from '@/features/analytics/lib/format';

type Props = { data: PostingFrequencyPoint[] };

export function PostingFrequencyChart({ data }: Props) {
  const rows = data.map((d) => ({ ...d, label: shortDate(d.date) }));

  return (
    <ChartShell title="Posting frequency" subtitle="Posts per day in range">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={rows} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 10 }} tickLine={false} />
          <YAxis allowDecimals={false} tick={{ fill: chartColors.axis, fontSize: 11 }} tickLine={false} width={28} />
          <Tooltip content={<ChartTooltip />} />
          <Bar dataKey="posts" name="Posts" fill={chartColors.emerald} radius={[4, 4, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

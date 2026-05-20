import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { PostingTimePoint } from '@/features/analytics/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import { hourLabel } from '@/features/analytics/lib/format';

type Props = { data: PostingTimePoint[] };

export function PostingTimeChart({ data }: Props) {
  const fullHours = Array.from({ length: 24 }, (_, hour) => {
    const hit = data.find((d) => d.hour === hour);
    return {
      hour,
      label: hourLabel(hour),
      avg_normalized: hit?.avg_normalized ?? 0,
      sample_count: hit?.sample_count ?? 0,
    };
  });

  return (
    <ChartShell
      title="Posting timeline"
      subtitle="Hour-of-day engagement profile (UTC)"
      aiHint="Timing"
    >
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={fullHours} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 9 }} tickLine={false} interval={2} />
          <YAxis tick={{ fill: chartColors.axis, fontSize: 11 }} tickLine={false} width={36} />
          <Tooltip content={<ChartTooltip />} />
          <Bar dataKey="avg_normalized" name="Avg normalized" fill={chartColors.violet} radius={[3, 3, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

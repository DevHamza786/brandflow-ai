import {
  Area,
  AreaChart,
  CartesianGrid,
  Legend,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { EngagementSeriesPoint } from '@/features/analytics/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import { shortDate } from '@/features/analytics/lib/format';

type Props = { data: EngagementSeriesPoint[] };

export function EngagementChart({ data }: Props) {
  const rows = data.map((d) => ({
    ...d,
    label: shortDate(d.date),
  }));

  return (
    <ChartShell
      title="Audience engagement"
      subtitle="Impressions and interactions over time"
      aiHint="Signal"
    >
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart data={rows} margin={chartMargin}>
          <defs>
            <linearGradient id="engImpressions" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stopColor={chartColors.accent} stopOpacity={0.35} />
              <stop offset="100%" stopColor={chartColors.accent} stopOpacity={0} />
            </linearGradient>
            <linearGradient id="engLikes" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stopColor={chartColors.emerald} stopOpacity={0.3} />
              <stop offset="100%" stopColor={chartColors.emerald} stopOpacity={0} />
            </linearGradient>
          </defs>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 11 }} tickLine={false} />
          <YAxis tick={{ fill: chartColors.axis, fontSize: 11 }} tickLine={false} width={40} />
          <Tooltip content={<ChartTooltip />} />
          <Legend wrapperStyle={{ fontSize: 11, color: chartColors.axis }} />
          <Area
            type="monotone"
            dataKey="impressions"
            name="Impressions"
            stroke={chartColors.accent}
            fill="url(#engImpressions)"
            strokeWidth={2}
          />
          <Area
            type="monotone"
            dataKey="likes"
            name="Likes"
            stroke={chartColors.emerald}
            fill="url(#engLikes)"
            strokeWidth={2}
          />
        </AreaChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

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
import type { CompetitorBenchmark } from '@/features/competitors/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import { formatEngagementRate, formatPercent } from '@/features/analytics/lib/format';

type Props = { benchmark: CompetitorBenchmark };

export function EngagementComparisonChart({ benchmark }: Props) {
  const rows = [
    {
      label: 'You',
      rate: benchmark.workspace_avg_engagement_rate,
      posts: benchmark.workspace_posts_observed,
    },
    {
      label: 'Competitor',
      rate: benchmark.competitor_avg_engagement_rate,
      posts: benchmark.competitor_posts_observed,
    },
  ];

  return (
    <ChartShell
      title="Engagement benchmark"
      subtitle="Competitor vs your workspace post snapshots"
      aiHint="Benchmark"
    >
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={rows} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 12 }} tickLine={false} />
          <YAxis
            tickFormatter={(v) => formatPercent(v as number, 1)}
            tick={{ fill: chartColors.axis, fontSize: 11 }}
            tickLine={false}
            width={48}
          />
          <Tooltip
            content={<ChartTooltip />}
            formatter={(v: number) => formatEngagementRate(v)}
          />
          <Legend wrapperStyle={{ fontSize: 11, color: chartColors.axis }} />
          <Bar dataKey="rate" name="Avg engagement rate" fill={chartColors.accent} radius={[4, 4, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
      {benchmark.delta_pct != null && (
        <p className="px-4 pb-3 text-center text-xs text-slate-500">
          {benchmark.competitor_ahead ? 'Competitor ahead by ' : 'You lead by '}
          <span className={benchmark.competitor_ahead ? 'text-amber-300' : 'text-emerald-400'}>
            {Math.abs(benchmark.delta_pct).toFixed(1)}%
          </span>
        </p>
      )}
    </ChartShell>
  );
}

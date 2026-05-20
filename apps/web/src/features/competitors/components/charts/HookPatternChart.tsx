import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { HookStyleRow } from '@/features/competitors/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import { formatPercent } from '@/features/analytics/lib/format';

type Props = { styles: HookStyleRow[] };

export function HookPatternChart({ styles }: Props) {
  const rows = styles.map((s) => ({
    label: s.label,
    rate: s.avg_engagement_rate,
    uplift: s.uplift_pct_vs_snapshot,
    count: s.sample_count,
  }));

  if (rows.length === 0) {
    return (
      <ChartShell title="Hook pattern analysis" subtitle="Style-level engagement in snapshot">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          Need more posts with classifiable hooks
        </div>
      </ChartShell>
    );
  }

  return (
    <ChartShell
      title="Hook pattern analysis"
      subtitle="Avg engagement rate by hook style"
      aiHint="Pattern"
      heightClass="h-[min(360px,50vw)] min-h-[240px]"
    >
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={rows} layout="vertical" margin={{ ...chartMargin, left: 8 }}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" horizontal={false} />
          <XAxis
            type="number"
            tickFormatter={(v) => formatPercent(v as number, 1)}
            tick={{ fill: chartColors.axis, fontSize: 11 }}
            tickLine={false}
          />
          <YAxis
            type="category"
            dataKey="label"
            width={100}
            tick={{ fill: chartColors.axis, fontSize: 10 }}
            tickLine={false}
          />
          <Tooltip content={<ChartTooltip />} />
          <Bar dataKey="rate" name="Engagement rate" fill={chartColors.accent} radius={[0, 4, 4, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

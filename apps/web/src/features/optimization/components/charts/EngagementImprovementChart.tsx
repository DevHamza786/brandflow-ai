import {
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import type { EngagementImprovementPoint } from '@/features/optimization/types/dashboard';

const COLORS = [chartColors.emerald, chartColors.accent, chartColors.violet, chartColors.amber];

type Props = { data: EngagementImprovementPoint[] };

export function EngagementImprovementChart({ data }: Props) {
  if (data.length === 0) {
    return (
      <ChartShell title="Engagement improvements" subtitle="Per-engine uplift from latest cycle" aiHint="Adaptive">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          Run an optimization cycle to populate engine uplifts.
        </div>
      </ChartShell>
    );
  }

  return (
    <ChartShell
      title="Engagement improvements"
      subtitle="Uplift % by optimization engine (latest signals)"
      aiHint="Adaptive"
    >
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={data} layout="vertical" margin={{ ...chartMargin, left: 8 }}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" horizontal={false} />
          <XAxis type="number" tick={{ fill: chartColors.axis, fontSize: 11 }} unit="%" />
          <YAxis
            type="category"
            dataKey="label"
            width={88}
            tick={{ fill: chartColors.axis, fontSize: 11 }}
          />
          <Tooltip content={<ChartTooltip />} />
          <Bar dataKey="uplift_pct" name="Uplift %" radius={[0, 4, 4, 0]}>
            {data.map((_, i) => (
              <Cell key={i} fill={COLORS[i % COLORS.length]} />
            ))}
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

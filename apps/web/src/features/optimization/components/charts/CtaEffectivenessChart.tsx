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
import type { CtaEffectivenessPoint } from '@/features/optimization/types/dashboard';

type Props = { data: CtaEffectivenessPoint[] };

export function CtaEffectivenessChart({ data }: Props) {
  if (data.length === 0) {
    return (
      <ChartShell title="CTA effectiveness" subtitle="With vs without preferred CTA" aiHint="CTA">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          Configure preferred CTAs in brand profile and run a cycle.
        </div>
      </ChartShell>
    );
  }

  const chartData = data.map((d) => ({
    name: d.label.length > 18 ? `${d.label.slice(0, 16)}…` : d.label,
    with_cta: d.with_cta_avg,
    without_cta: d.without_cta_avg,
    uplift_pct: d.uplift_pct,
  }));

  return (
    <ChartShell
      title="CTA effectiveness"
      subtitle="Normalized engagement: posts with vs without preferred CTA"
      aiHint="CTA"
    >
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={chartData} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="name" tick={{ fill: chartColors.axis, fontSize: 10 }} />
          <YAxis tick={{ fill: chartColors.axis, fontSize: 11 }} />
          <Tooltip content={<ChartTooltip />} />
          <Legend wrapperStyle={{ fontSize: 11, color: chartColors.axis }} />
          <Bar dataKey="with_cta" name="With CTA" fill={chartColors.emerald} radius={[3, 3, 0, 0]} />
          <Bar dataKey="without_cta" name="Without CTA" fill={chartColors.slate} radius={[3, 3, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

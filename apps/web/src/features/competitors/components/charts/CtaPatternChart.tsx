import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';
import type { CompetitorSnapshotView } from '@/features/competitors/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors } from '@/features/analytics/lib/chartTheme';

const COLORS = [chartColors.emerald, chartColors.accent, chartColors.violet, chartColors.amber];

type Props = { snapshot: CompetitorSnapshotView };

export function CtaPatternChart({ snapshot }: Props) {
  const top = snapshot.cta_patterns.top_ctas ?? [];
  const rows = top.filter((t) => t.count > 0);

  return (
    <ChartShell title="CTA patterns" subtitle="Most frequent calls-to-action">
      {rows.length === 0 ? (
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          No CTA text detected in posts
        </div>
      ) : (
        <ResponsiveContainer width="100%" height="100%">
          <PieChart>
            <Pie
              data={rows}
              dataKey="count"
              nameKey="cta"
              cx="50%"
              cy="50%"
              innerRadius="50%"
              outerRadius="78%"
              paddingAngle={2}
            >
              {rows.map((_, i) => (
                <Cell key={i} fill={COLORS[i % COLORS.length]} stroke="transparent" />
              ))}
            </Pie>
            <Tooltip content={<ChartTooltip />} />
          </PieChart>
        </ResponsiveContainer>
      )}
    </ChartShell>
  );
}

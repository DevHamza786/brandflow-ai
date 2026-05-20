import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { TopHookRow } from '@/features/analytics/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';

type Props = { hooks: TopHookRow[] };

function hookLabel(row: TopHookRow, index: number): string {
  const text = row.hook_text?.trim();
  if (!text) {
    return `#${index + 1}`;
  }
  return text.length > 28 ? `${text.slice(0, 28)}…` : text;
}

export function HookPerformanceChart({ hooks }: Props) {
  const rows = hooks.slice(0, 8).map((h, i) => ({
    label: hookLabel(h, i),
    normalized: h.normalized ?? 0,
    hook_score: h.hook_score ?? 0,
  }));

  return (
    <ChartShell
      title="Hook performance"
      subtitle="Normalized engagement vs lab score"
      aiHint="Hook Lab"
      heightClass="h-[min(360px,50vw)] min-h-[240px]"
    >
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={rows} layout="vertical" margin={{ ...chartMargin, left: 8 }}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" horizontal={false} />
          <XAxis type="number" tick={{ fill: chartColors.axis, fontSize: 11 }} tickLine={false} />
          <YAxis
            type="category"
            dataKey="label"
            width={100}
            tick={{ fill: chartColors.axis, fontSize: 10 }}
            tickLine={false}
          />
          <Tooltip content={<ChartTooltip />} />
          <Bar dataKey="normalized" name="Normalized" fill={chartColors.accent} radius={[0, 4, 4, 0]} />
          <Bar dataKey="hook_score" name="Hook score" fill={chartColors.violet} radius={[0, 4, 4, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

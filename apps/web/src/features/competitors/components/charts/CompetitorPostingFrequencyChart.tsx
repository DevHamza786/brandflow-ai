import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { CompetitorSnapshotView } from '@/features/competitors/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors, chartMargin } from '@/features/analytics/lib/chartTheme';
import { hourLabel } from '@/features/analytics/lib/format';

type Props = { snapshot: CompetitorSnapshotView };

export function CompetitorPostingFrequencyChart({ snapshot }: Props) {
  const histogram = snapshot.posting_cadence.hour_histogram ?? [];
  const rows = histogram.map((h) => ({
    label: hourLabel(h.hour),
    posts: h.post_count,
  }));

  if (rows.length === 0) {
    return (
      <ChartShell title="Posting timeline" subtitle="Hour-of-day distribution (UTC)">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          No posting timestamps in snapshot
        </div>
      </ChartShell>
    );
  }

  return (
    <ChartShell title="Posting timeline" subtitle="Posts by hour (UTC)" aiHint="Cadence">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={rows} margin={chartMargin}>
          <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" vertical={false} />
          <XAxis dataKey="label" tick={{ fill: chartColors.axis, fontSize: 9 }} interval={2} tickLine={false} />
          <YAxis allowDecimals={false} tick={{ fill: chartColors.axis, fontSize: 11 }} width={28} tickLine={false} />
          <Tooltip content={<ChartTooltip />} />
          <Bar dataKey="posts" name="Posts" fill={chartColors.violet} radius={[3, 3, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

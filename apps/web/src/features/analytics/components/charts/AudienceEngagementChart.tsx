import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';
import type { AudienceOverview } from '@/features/analytics/types/dashboard';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import { chartColors } from '@/features/analytics/lib/chartTheme';
import { formatCompact } from '@/features/analytics/lib/format';

const SLICE_COLORS = [
  chartColors.emerald,
  chartColors.accent,
  chartColors.violet,
  chartColors.amber,
];

type Props = { overview: AudienceOverview };

export function AudienceEngagementChart({ overview }: Props) {
  const mix = overview.interaction_mix;
  const rows = [
    { name: 'Likes', value: mix.likes },
    { name: 'Comments', value: mix.comments },
    { name: 'Reposts', value: mix.reposts },
    { name: 'Saves', value: mix.saves },
  ].filter((r) => r.value > 0);

  const empty = rows.length === 0;

  return (
    <ChartShell title="Engagement mix" subtitle="Interaction breakdown">
      {empty ? (
        <div className="flex h-full items-center justify-center text-sm text-slate-500">
          No interactions in this range
        </div>
      ) : (
        <div className="flex h-full flex-col gap-2 sm:flex-row sm:items-center">
          <div className="min-h-[200px] flex-1">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={rows}
                  dataKey="value"
                  nameKey="name"
                  cx="50%"
                  cy="50%"
                  innerRadius="55%"
                  outerRadius="80%"
                  paddingAngle={2}
                >
                  {rows.map((_, i) => (
                    <Cell key={i} fill={SLICE_COLORS[i % SLICE_COLORS.length]} stroke="transparent" />
                  ))}
                </Pie>
                <Tooltip content={<ChartTooltip />} />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <dl className="grid shrink-0 grid-cols-2 gap-3 px-2 text-xs sm:grid-cols-1">
            <div>
              <dt className="text-slate-500">Total interactions</dt>
              <dd className="font-mono text-lg text-white">{formatCompact(overview.total_interactions)}</dd>
            </div>
            <div>
              <dt className="text-slate-500">Avg impressions / post</dt>
              <dd className="font-mono text-lg text-white">
                {formatCompact(overview.avg_impressions_per_post)}
              </dd>
            </div>
          </dl>
        </div>
      )}
    </ChartShell>
  );
}

import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';
import { ChartShell } from '@/features/analytics/components/charts/ChartShell';
import { ChartTooltip } from '@/features/analytics/components/charts/ChartTooltip';
import type { DecisionOutcomePoint } from '@/features/autonomous/types/dashboard';

type Props = { data: DecisionOutcomePoint[] };

export function DecisionOutcomesChart({ data }: Props) {
  if (data.length === 0) {
    return (
      <ChartShell title="Decision outcomes" aiHint="Outcomes">
        <div className="flex h-full items-center justify-center text-sm text-slate-500">No outcomes yet.</div>
      </ChartShell>
    );
  }

  return (
    <ChartShell title="Decision outcomes" subtitle="Blocked vs approved vs proposed" aiHint="Outcomes">
      <ResponsiveContainer width="100%" height="100%">
        <PieChart>
          <Tooltip content={<ChartTooltip />} />
          <Pie data={data} dataKey="count" nameKey="label" innerRadius="55%" outerRadius="85%" paddingAngle={2}>
            {data.map((entry, i) => (
              <Cell key={i} fill={entry.fill} />
            ))}
          </Pie>
        </PieChart>
      </ResponsiveContainer>
    </ChartShell>
  );
}

import type { TooltipProps } from 'recharts';
import { chartColors } from '@/features/analytics/lib/chartTheme';

export function ChartTooltip({
  active,
  payload,
  label,
}: TooltipProps<number, string>) {
  if (!active || !payload?.length) {
    return null;
  }

  return (
    <div
      className="rounded-lg border px-3 py-2 text-xs shadow-lg"
      style={{
        backgroundColor: chartColors.tooltipBg,
        borderColor: chartColors.tooltipBorder,
      }}
    >
      {label != null && <p className="mb-1 font-medium text-slate-200">{String(label)}</p>}
      <ul className="space-y-0.5">
        {payload.map((entry) => (
          <li key={entry.name} className="flex gap-2 text-slate-400">
            <span
              className="inline-block h-2 w-2 shrink-0 rounded-full"
              style={{ backgroundColor: entry.color }}
            />
            <span>
              {entry.name}:{' '}
              <span className="font-mono text-slate-200">{entry.value}</span>
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
}

import type { TopHookRow } from '@/features/analytics/types/dashboard';
import { formatCompact } from '@/features/analytics/lib/format';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { EmptyState } from '@/shared/components/feedback/EmptyState';

type Props = { hooks: TopHookRow[] };

export function TopHooksSection({ hooks }: Props) {
  return (
    <Card className="border-border/80">
      <CardHeader>
        <h2 className="text-sm font-medium text-slate-200">Top-performing hooks</h2>
        <p className="mt-1 text-xs text-slate-500">
          Ranked by normalized engagement — ready for future recommendation signals.
        </p>
      </CardHeader>
      <CardBody>
        {hooks.length === 0 ? (
          <EmptyState
            title="No hook performance yet"
            description="Score hooks in Hook Lab or publish posts with tracked metrics to populate this list."
          />
        ) : (
          <ul className="divide-y divide-border/60">
            {hooks.map((h, i) => (
              <li key={h.id} className="flex gap-4 py-4 first:pt-0 last:pb-0">
                <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent/10 font-mono text-sm text-accent">
                  {i + 1}
                </span>
                <div className="min-w-0 flex-1">
                  <p className="text-sm leading-relaxed text-slate-200">
                    {h.hook_text?.trim() || (
                      <span className="italic text-slate-500">Hook text unavailable</span>
                    )}
                  </p>
                  <div className="mt-2 flex flex-wrap gap-3 font-mono text-xs text-slate-500">
                    {h.normalized != null && (
                      <span>
                        Norm <span className="text-accent">{h.normalized.toFixed(3)}</span>
                      </span>
                    )}
                    {h.hook_score != null && (
                      <span>
                        Score <span className="text-amber-300">{h.hook_score.toFixed(1)}</span>
                      </span>
                    )}
                    <span>{formatCompact(h.impressions)} impr.</span>
                    <span>{formatCompact(h.likes)} likes</span>
                  </div>
                </div>
              </li>
            ))}
          </ul>
        )}
      </CardBody>
    </Card>
  );
}

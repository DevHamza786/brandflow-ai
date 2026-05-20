import type { CompetitorPostRow } from '@/features/competitors/types/dashboard';
import { formatCompact, formatEngagementRate } from '@/features/analytics/lib/format';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { EmptyState } from '@/shared/components/feedback/EmptyState';

type Props = { posts: CompetitorPostRow[] };

export function TopCompetitorPostsSection({ posts }: Props) {
  return (
    <Card className="border-border/80">
      <CardHeader>
        <h2 className="text-sm font-medium text-slate-200">Top-performing competitor posts</h2>
        <p className="mt-1 text-xs text-slate-500">Ranked by engagement rate from latest snapshot</p>
      </CardHeader>
      <CardBody>
        {posts.length === 0 ? (
          <EmptyState
            title="No posts in snapshot"
            description="Ingest a snapshot with posts[] via the API to populate this section."
          />
        ) : (
          <ul className="divide-y divide-border/60">
            {posts.map((p, i) => (
              <li key={p.post_id || i} className="flex gap-4 py-4 first:pt-0 last:pb-0">
                <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-500/15 font-mono text-sm text-violet-300">
                  {i + 1}
                </span>
                <div className="min-w-0 flex-1">
                  <p className="text-sm leading-relaxed text-slate-200">
                    {p.hook_text.trim() || (
                      <span className="italic text-slate-500">No hook text</span>
                    )}
                  </p>
                  <div className="mt-2 flex flex-wrap gap-3 font-mono text-xs text-slate-500">
                    <span>
                      Rate <span className="text-accent">{formatEngagementRate(p.engagement_rate)}</span>
                    </span>
                    <span>{formatCompact(p.impressions)} impr.</span>
                    <span>{formatCompact(p.likes)} likes</span>
                    {p.cta_text && <span className="text-amber-300/90">CTA: {p.cta_text}</span>}
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

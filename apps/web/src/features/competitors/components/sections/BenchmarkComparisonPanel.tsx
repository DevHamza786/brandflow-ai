import type { CompetitorBenchmark } from '@/features/competitors/types/dashboard';
import { formatEngagementRate, formatDeltaPct } from '@/features/analytics/lib/format';
import { Card, CardBody } from '@/shared/components/ui/Card';
import { cn } from '@/shared/lib/cn';

type Props = { benchmark: CompetitorBenchmark };

export function BenchmarkComparisonPanel({ benchmark }: Props) {
  const ahead = benchmark.competitor_ahead;

  return (
    <Card className="border-border/80 bg-gradient-to-br from-surface-overlay/60 to-surface-raised/40">
      <CardBody className="space-y-4">
        <div>
          <h2 className="text-sm font-medium text-slate-200">Benchmark comparison</h2>
          <p className="mt-1 text-xs text-slate-500">
            Workspace-owned posts vs this competitor&apos;s latest snapshot
          </p>
        </div>
        <div className="grid gap-4 sm:grid-cols-3">
          <div>
            <p className="text-xs uppercase tracking-wider text-slate-500">Your avg rate</p>
            <p className="mt-1 font-mono text-xl text-white">
              {formatEngagementRate(benchmark.workspace_avg_engagement_rate)}
            </p>
            <p className="text-xs text-slate-600">{benchmark.workspace_posts_observed} posts</p>
          </div>
          <div>
            <p className="text-xs uppercase tracking-wider text-slate-500">Competitor avg</p>
            <p className="mt-1 font-mono text-xl text-white">
              {formatEngagementRate(benchmark.competitor_avg_engagement_rate)}
            </p>
            <p className="text-xs text-slate-600">{benchmark.competitor_posts_observed} posts</p>
          </div>
          <div>
            <p className="text-xs uppercase tracking-wider text-slate-500">Gap</p>
            <p
              className={cn(
                'mt-1 font-mono text-xl',
                ahead ? 'text-amber-300' : 'text-emerald-400',
              )}
            >
              {formatDeltaPct(benchmark.delta_pct)}
            </p>
            <p className="text-xs text-slate-600">{ahead ? 'Competitor ahead' : 'You lead'}</p>
          </div>
        </div>
      </CardBody>
    </Card>
  );
}

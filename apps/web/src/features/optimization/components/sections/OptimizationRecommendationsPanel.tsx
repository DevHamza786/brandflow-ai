import type { OptimizationRecommendationDto } from '@/features/optimization/types/dashboard';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { Badge } from '@/shared/components/ui/Badge';
import { EmptyState } from '@/shared/components/feedback/EmptyState';

type Props = {
  recommendations: OptimizationRecommendationDto[];
};

export function OptimizationRecommendationsPanel({ recommendations }: Props) {
  return (
    <Card className="border-emerald-500/25 bg-emerald-500/5">
      <CardHeader>
        <div className="flex flex-wrap items-center gap-2">
          <h2 className="text-sm font-medium text-slate-200">Optimization recommendations</h2>
          <Badge className="border-emerald-500/40 bg-emerald-500/15 text-emerald-300">
            Adoption-ready
          </Badge>
        </div>
        <p className="mt-1 text-xs text-slate-500">
          Synced from optimization cycles — compatible with agents, workflows, and future experiment
          runners
        </p>
      </CardHeader>
      <CardBody>
        {recommendations.length === 0 ? (
          <EmptyState
            title="No optimization recommendations yet"
            description="Run an optimization cycle after collecting post performance snapshots. High-confidence signals sync here automatically."
          />
        ) : (
          <ul className="divide-y divide-border/60">
            {recommendations.map((r) => (
              <li key={r.id} className="py-4 first:pt-0 last:pb-0">
                <div className="flex flex-wrap items-start justify-between gap-2">
                  <div>
                    <p className="font-medium text-slate-200">{r.title}</p>
                    <p className="mt-0.5 text-xs text-slate-500">
                      {r.type.replace(/_/g, ' ')}
                      {r.cycle_number != null && (
                        <span className="text-emerald-400/90"> · Cycle {r.cycle_number}</span>
                      )}
                    </p>
                  </div>
                  <div className="text-right">
                    <span className="font-mono text-xs text-emerald-300">Score {r.score}</span>
                    {r.confidence != null && (
                      <p className="text-[10px] text-slate-500">
                        {(r.confidence * 100).toFixed(0)}% conf.
                      </p>
                    )}
                  </div>
                </div>
                <p className="mt-2 text-sm leading-relaxed text-slate-400">{r.summary}</p>
                {typeof r.evidence.uplift_pct === 'number' && (
                  <p className="mt-2 text-xs text-emerald-400/90">
                    Evidence uplift: +{Number(r.evidence.uplift_pct).toFixed(1)}%
                    {typeof r.evidence.sample_size === 'number' &&
                      ` · n=${r.evidence.sample_size}`}
                  </p>
                )}
              </li>
            ))}
          </ul>
        )}
      </CardBody>
    </Card>
  );
}

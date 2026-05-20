import type {
  CompetitorRecommendation,
  HookPatternInsight,
} from '@/features/competitors/types/dashboard';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { EmptyState } from '@/shared/components/feedback/EmptyState';
import { Badge } from '@/shared/components/ui/Badge';

type Props = {
  recommendations: CompetitorRecommendation[];
  insights: HookPatternInsight[];
  hints: { type: string; text: string }[];
};

export function CompetitorRecommendationsSection({
  recommendations,
  insights,
  hints,
}: Props) {
  const hasContent = recommendations.length > 0 || insights.length > 0 || hints.length > 0;

  return (
    <Card className="border-accent/20 bg-accent/5">
      <CardHeader>
        <div className="flex items-center gap-2">
          <h2 className="text-sm font-medium text-slate-200">Competitive intelligence</h2>
          <Badge className="border-accent/40 bg-accent/15 text-accent">AI signals</Badge>
        </div>
        <p className="mt-1 text-xs text-slate-500">
          Pattern insights and persisted recommendations — feeds optimization workflows
        </p>
      </CardHeader>
      <CardBody className="space-y-6">
        {!hasContent ? (
          <EmptyState
            title="No competitive recommendations yet"
            description="Run POST /recommendations/generate after ingesting snapshots, or ingest richer post data for style-gap detection."
          />
        ) : (
          <>
            {insights.length > 0 && (
              <ul className="space-y-3">
                {insights.map((insight, i) => (
                  <li
                    key={i}
                    className="rounded-lg border border-accent/25 bg-surface-raised/80 px-4 py-3 text-sm leading-relaxed text-slate-300"
                  >
                    {insight.summary}
                  </li>
                ))}
              </ul>
            )}
            {hints.map((h, i) => (
              <p key={`hint-${i}`} className="text-sm text-slate-400">
                <span className="font-medium text-accent">{h.type}:</span> {h.text}
              </p>
            ))}
            {recommendations.length > 0 && (
              <ul className="divide-y divide-border/60">
                {recommendations.map((r) => (
                  <li key={r.id} className="py-4 first:pt-0 last:pb-0">
                    <div className="flex flex-wrap items-start justify-between gap-2">
                      <p className="font-medium text-slate-200">{r.title}</p>
                      <span className="font-mono text-xs text-accent">Score {r.score}</span>
                    </div>
                    <p className="mt-2 text-sm text-slate-400">{r.summary}</p>
                  </li>
                ))}
              </ul>
            )}
          </>
        )}
      </CardBody>
    </Card>
  );
}

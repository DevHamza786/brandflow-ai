import type { CompetitorTrends } from '@/features/competitors/types/dashboard';
import { formatDeltaPct } from '@/features/analytics/lib/format';
import { Card, CardBody } from '@/shared/components/ui/Card';
import { cn } from '@/shared/lib/cn';

type Props = { trends: CompetitorTrends };

function Metric({
  label,
  value,
  tone,
}: {
  label: string;
  value: string;
  tone?: 'up' | 'down' | 'neutral';
}) {
  return (
    <div className="rounded-lg border border-border/60 bg-surface/50 px-4 py-3">
      <p className="text-xs text-slate-500">{label}</p>
      <p
        className={cn(
          'mt-1 font-mono text-lg font-semibold',
          tone === 'up' && 'text-emerald-400',
          tone === 'down' && 'text-rose-400',
          tone === 'neutral' && 'text-slate-200',
        )}
      >
        {value}
      </p>
    </div>
  );
}

export function TrendDetectionPanel({ trends }: Props) {
  if (trends.status === 'insufficient_history') {
    return (
      <Card className="border-dashed border-border">
        <CardBody>
          <p className="text-sm text-slate-400">
            Trend detection activates after a second snapshot ingest — capture competitive movement over
            time.
          </p>
        </CardBody>
      </Card>
    );
  }

  const engDelta = trends.engagement_rate_delta_pct;
  const cadenceDelta = trends.posts_per_week_delta;

  return (
    <Card className="border-border/80">
      <CardBody className="space-y-4">
        <div>
          <h2 className="text-sm font-medium text-slate-200">Trend detection</h2>
          <p className="mt-1 text-xs text-slate-500">
            Deltas vs previous snapshot
            {trends.previous_captured_at && ` (${new Date(trends.previous_captured_at).toLocaleDateString()})`}
          </p>
        </div>
        <div className="grid gap-3 sm:grid-cols-3">
          <Metric
            label="Engagement rate"
            value={formatDeltaPct(engDelta)}
            tone={engDelta != null && engDelta > 0 ? 'up' : engDelta != null && engDelta < 0 ? 'down' : 'neutral'}
          />
          <Metric
            label="Posts / week"
            value={cadenceDelta != null ? `${cadenceDelta > 0 ? '+' : ''}${cadenceDelta.toFixed(2)}` : '—'}
            tone={
              cadenceDelta != null && cadenceDelta > 0 ? 'up' : cadenceDelta != null && cadenceDelta < 0 ? 'down' : 'neutral'
            }
          />
          <Metric
            label="Intelligence score"
            value={
              trends.intelligence_score_delta != null
                ? `${trends.intelligence_score_delta > 0 ? '+' : ''}${trends.intelligence_score_delta.toFixed(1)}`
                : '—'
            }
            tone={
              trends.intelligence_score_delta != null && trends.intelligence_score_delta > 0
                ? 'up'
                : trends.intelligence_score_delta != null && trends.intelligence_score_delta < 0
                  ? 'down'
                  : 'neutral'
            }
          />
        </div>
      </CardBody>
    </Card>
  );
}

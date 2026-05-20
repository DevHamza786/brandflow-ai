import type { HookResultsMetadata, HookResultsTimestamps } from '@/features/results/types/results.types';
import { Badge } from '@/shared/components/ui/Badge';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { STATUS_LABELS, statusBadgeClass } from '@/shared/workflow/status';
import type { WorkflowPollingStatus } from '@/shared/types/api';
import { cn } from '@/shared/lib/cn';

type Props = {
  status: WorkflowPollingStatus;
  metadata: HookResultsMetadata;
  timestamps: HookResultsTimestamps;
  overallScore: number | null;
  agentRunId: string;
  className?: string;
};

function formatTs(value: string | null): string {
  if (!value) return '—';
  try {
    return new Intl.DateTimeFormat(undefined, {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(value));
  } catch {
    return value;
  }
}

function MetaRow({ label, value }: { label: string; value: string | null }) {
  if (!value) return null;
  return (
    <div>
      <dt className="text-xs uppercase tracking-wide text-slate-500">{label}</dt>
      <dd className="mt-1 break-all font-mono text-xs text-slate-300">{value}</dd>
    </div>
  );
}

export function ResultsMetadata({
  status,
  metadata,
  timestamps,
  overallScore,
  agentRunId,
  className,
}: Props) {
  return (
    <Card className={cn(className)}>
      <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 className="text-sm font-semibold text-slate-200">Run metadata</h3>
          <p className="mt-1 font-mono text-xs text-slate-500">{agentRunId}</p>
        </div>
        <Badge className={statusBadgeClass(status)}>{STATUS_LABELS[status]}</Badge>
      </CardHeader>
      <CardBody className="space-y-6">
        {overallScore !== null && (
          <div className="rounded-lg border border-accent/25 bg-accent/5 px-4 py-3">
            <p className="text-xs uppercase tracking-wide text-slate-500">Primary overall score</p>
            <p className="mt-1 font-mono text-3xl font-semibold text-white">{overallScore.toFixed(1)}</p>
          </div>
        )}

        <dl className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <MetaRow label="Provider" value={metadata.provider} />
          <MetaRow label="Model" value={metadata.model} />
          <MetaRow label="Prompt version" value={metadata.promptVersion} />
          <MetaRow label="Workflow" value={metadata.workflowSlug} />
          <MetaRow label="Agent" value={metadata.agentSlug} />
          <MetaRow label="Started" value={formatTs(timestamps.startedAt)} />
          <MetaRow label="Completed" value={formatTs(timestamps.completedAt)} />
        </dl>

        <div className="rounded-lg border border-dashed border-border/80 bg-surface-overlay/30 px-4 py-3">
          <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
            Experiment (A/B ready)
          </p>
          <dl className="mt-2 grid gap-3 sm:grid-cols-2">
            <div>
              <dt className="text-xs uppercase tracking-wide text-slate-500">Experiment ID</dt>
              <dd className="mt-1 font-mono text-xs text-slate-400">
                {metadata.experimentId ?? '—'}
              </dd>
            </div>
            <div>
              <dt className="text-xs uppercase tracking-wide text-slate-500">Variant label</dt>
              <dd className="mt-1 text-xs text-slate-400">
                {metadata.experimentVariant ?? 'Not assigned'}
              </dd>
            </div>
          </dl>
        </div>
      </CardBody>
    </Card>
  );
}

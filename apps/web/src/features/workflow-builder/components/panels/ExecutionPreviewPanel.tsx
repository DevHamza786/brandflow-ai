import type { ExecuteBlueprintResult } from '@/features/workflow-builder/types/workflowBuilder.types';
import { Spinner } from '@/shared/components/ui/Spinner';

interface Props {
  result: ExecuteBlueprintResult | undefined;
  isRunning: boolean;
}

export function ExecutionPreviewPanel({ result, isRunning }: Props) {
  return (
    <div className="rounded-xl border border-border bg-surface-overlay p-5">
      <p className="text-sm font-medium text-slate-200">Execution preview</p>
      {isRunning ? (
        <div className="mt-4 flex items-center gap-2 text-sm text-slate-400">
          <Spinner className="h-4 w-4" />
          Running blueprint…
        </div>
      ) : null}
      {result ? (
        <dl className="mt-4 space-y-2 font-mono text-xs">
          <Row label="nodes_executed" value={String(result.nodes_executed)} />
          <Row label="executed" value={result.executed_node_keys.join(' → ')} />
          {result.failed_node_keys.length > 0 ? (
            <Row label="failed" value={result.failed_node_keys.join(', ')} highlight="fail" />
          ) : null}
          {result.skipped_node_keys.length > 0 ? (
            <Row label="skipped" value={result.skipped_node_keys.join(', ')} />
          ) : null}
          <Row label="trace_id" value={result.trace_id} />
        </dl>
      ) : (
        <p className="mt-3 text-xs text-slate-500">
          Run a preview execution to see node outcomes without full drag-and-drop editing.
        </p>
      )}
    </div>
  );
}

function Row({
  label,
  value,
  highlight,
}: {
  label: string;
  value: string;
  highlight?: 'fail';
}) {
  return (
    <div>
      <dt className="text-slate-500">{label}</dt>
      <dd className={highlight === 'fail' ? 'text-rose-300' : 'text-slate-300'}>{value}</dd>
    </div>
  );
}

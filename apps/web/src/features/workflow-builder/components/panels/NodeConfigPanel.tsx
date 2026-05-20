import type { WorkflowNodeDto } from '@/features/workflow-builder/types/workflowBuilder.types';
import { nodeMeta } from '@/features/workflow-builder/lib/nodeMeta';

interface Props {
  node: WorkflowNodeDto | null;
}

export function NodeConfigPanel({ node }: Props) {
  if (!node) {
    return (
      <div className="rounded-xl border border-border bg-surface-overlay p-5 text-sm text-slate-500">
        Select a node on the canvas to inspect configuration.
      </div>
    );
  }

  const meta = nodeMeta(node.node_type);

  return (
    <div className="rounded-xl border border-border bg-surface-overlay p-5">
      <p className="text-xs font-medium uppercase tracking-wider text-slate-500">Node config</p>
      <h3 className="mt-2 text-lg font-semibold text-white">{node.label ?? node.node_key}</h3>
      <p className={cnMeta(meta.accent)}>{meta.label}</p>
      <dl className="mt-4 space-y-3 font-mono text-xs">
        <div>
          <dt className="text-slate-500">node_key</dt>
          <dd className="text-slate-200">{node.node_key}</dd>
        </div>
        <div>
          <dt className="text-slate-500">node_type</dt>
          <dd className="text-slate-200">{node.node_type}</dd>
        </div>
        <div>
          <dt className="text-slate-500">config</dt>
          <dd className="mt-1 max-h-40 overflow-auto rounded-lg bg-surface-raised p-2 text-slate-300">
            <pre>{JSON.stringify(node.config, null, 2)}</pre>
          </dd>
        </div>
      </dl>
    </div>
  );
}

function cnMeta(accent: string) {
  return `mt-1 text-sm ${accent}`;
}

import type { WorkflowBlueprintDto } from '@/features/workflow-builder/types/workflowBuilder.types';

interface Props {
  blueprint: WorkflowBlueprintDto | null;
}

export function WorkflowHistoryPanel({ blueprint }: Props) {
  if (!blueprint) {
    return null;
  }

  return (
    <div className="rounded-xl border border-border bg-surface-overlay p-5">
      <p className="text-sm font-medium text-slate-200">Blueprint history</p>
      <ul className="mt-3 space-y-2 text-xs text-slate-400">
        <li>
          <span className="text-slate-500">slug · </span>
          {blueprint.slug}
        </li>
        <li>
          <span className="text-slate-500">version · </span>v{blueprint.version}
        </li>
        <li>
          <span className="text-slate-500">status · </span>
          {blueprint.status}
        </li>
        <li>
          <span className="text-slate-500">type · </span>
          {blueprint.blueprint_type}
        </li>
      </ul>
      <p className="mt-4 text-[11px] text-slate-600">
        Execution runs link to workflow_runs for audit — full timeline UI in a later iteration.
      </p>
    </div>
  );
}

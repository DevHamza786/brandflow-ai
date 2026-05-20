import { cn } from '@/shared/lib/cn';
import { nodeMeta } from '@/features/workflow-builder/lib/nodeMeta';
import type { CanvasNodeLayout } from '@/features/workflow-builder/types/workflowBuilder.types';
import { CANVAS_NODE_SIZE } from '@/features/workflow-builder/lib/layoutNodes';

interface Props {
  layout: CanvasNodeLayout;
  selected: boolean;
  onSelect: (nodeKey: string) => void;
}

export function WorkflowNodeCard({ layout, selected, onSelect }: Props) {
  const meta = nodeMeta(layout.node.node_type);
  const title = layout.node.label ?? layout.node.node_key;

  return (
    <button
      type="button"
      className={cn(
        'absolute rounded-xl border bg-surface-overlay/95 p-4 text-left shadow-lg transition-all',
        'hover:border-accent/50 hover:shadow-glow',
        selected ? 'border-accent ring-2 ring-accent/30' : 'border-border',
      )}
      style={{
        left: layout.x,
        top: layout.y,
        width: CANVAS_NODE_SIZE.width,
        minHeight: CANVAS_NODE_SIZE.height,
      }}
      onClick={() => onSelect(layout.node.node_key)}
    >
      <div className="flex items-start justify-between gap-2">
        <span className={cn('font-mono text-lg', meta.accent)}>{meta.icon}</span>
        <span className="rounded bg-surface-raised px-1.5 py-0.5 font-mono text-[10px] uppercase tracking-wide text-slate-500">
          {layout.node.node_type}
        </span>
      </div>
      <p className="mt-2 text-sm font-semibold text-slate-100">{title}</p>
      <p className="mt-1 text-xs text-slate-500">{meta.label}</p>
    </button>
  );
}

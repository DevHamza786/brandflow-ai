import { WorkflowEdgeLayer } from '@/features/workflow-builder/components/edges/WorkflowEdgeLayer';
import { WorkflowNodeCard } from '@/features/workflow-builder/components/nodes/WorkflowNodeCard';
import type { CanvasNodeLayout, WorkflowEdgeDto } from '@/features/workflow-builder/types/workflowBuilder.types';

interface Props {
  layouts: CanvasNodeLayout[];
  edges: WorkflowEdgeDto[];
  selectedNodeKey: string | null;
  onSelectNode: (key: string) => void;
}

export function WorkflowCanvas({ layouts, edges, selectedNodeKey, onSelectNode }: Props) {
  const width = Math.max(
    900,
    ...layouts.map((l) => l.x + 280),
  );
  const height = Math.max(
    420,
    ...layouts.map((l) => l.y + 140),
  );

  return (
    <div className="relative overflow-auto rounded-2xl border border-border bg-[radial-gradient(circle_at_1px_1px,_#252b36_1px,_transparent_0)] bg-[length:24px_24px] bg-surface-raised">
      <div
        className="relative min-h-[420px]"
        style={{ width, height }}
        data-workflow-canvas
        role="img"
        aria-label="Workflow graph visualization"
      >
        <WorkflowEdgeLayer edges={edges} layouts={layouts} />
        {layouts.map((layout) => (
          <WorkflowNodeCard
            key={layout.node.node_key}
            layout={layout}
            selected={selectedNodeKey === layout.node.node_key}
            onSelect={onSelectNode}
          />
        ))}
        <p className="pointer-events-none absolute bottom-3 right-4 font-mono text-[10px] text-slate-600">
          Drag-and-drop foundation · read-only layout v1
        </p>
      </div>
    </div>
  );
}

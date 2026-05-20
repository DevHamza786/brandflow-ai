import type { CanvasNodeLayout, WorkflowEdgeDto } from '@/features/workflow-builder/types/workflowBuilder.types';
import { CANVAS_NODE_SIZE } from '@/features/workflow-builder/lib/layoutNodes';

interface Props {
  edges: WorkflowEdgeDto[];
  layouts: CanvasNodeLayout[];
}

export function WorkflowEdgeLayer({ edges, layouts }: Props) {
  const byKey = Object.fromEntries(layouts.map((l) => [l.node.node_key, l]));

  return (
    <svg className="pointer-events-none absolute inset-0 h-full w-full" aria-hidden>
      <defs>
        <marker
          id="edge-arrow"
          markerWidth="8"
          markerHeight="8"
          refX="6"
          refY="4"
          orient="auto"
        >
          <path d="M0,0 L8,4 L0,8 Z" fill="rgba(59, 158, 255, 0.6)" />
        </marker>
      </defs>
      {edges.map((edge) => {
        const from = byKey[edge.from_node_key];
        const to = byKey[edge.to_node_key];
        if (!from || !to) {
          return null;
        }

        const x1 = from.x + CANVAS_NODE_SIZE.width;
        const y1 = from.y + CANVAS_NODE_SIZE.height / 2;
        const x2 = to.x;
        const y2 = to.y + CANVAS_NODE_SIZE.height / 2;
        const midX = (x1 + x2) / 2;

        return (
          <path
            key={`${edge.from_node_key}-${edge.to_node_key}`}
            d={`M ${x1} ${y1} C ${midX} ${y1}, ${midX} ${y2}, ${x2} ${y2}`}
            fill="none"
            stroke="rgba(59, 158, 255, 0.45)"
            strokeWidth="2"
            markerEnd="url(#edge-arrow)"
          />
        );
      })}
    </svg>
  );
}

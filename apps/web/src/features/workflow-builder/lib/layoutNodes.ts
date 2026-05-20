import type { CanvasNodeLayout, WorkflowEdgeDto, WorkflowNodeDto } from '@/features/workflow-builder/types/workflowBuilder.types';

const NODE_WIDTH = 220;
const NODE_HEIGHT = 88;
const H_GAP = 72;
const V_GAP = 32;

/**
 * Auto-layout for visualization (drag-and-drop positions layered on top later).
 */
export function layoutWorkflowNodes(
  nodes: WorkflowNodeDto[],
  edges: WorkflowEdgeDto[],
): CanvasNodeLayout[] {
  const sorted = [...nodes].sort((a, b) => a.sort_order - b.sort_order);
  const order = topologicalKeys(sorted, edges);
  const layouts: CanvasNodeLayout[] = [];

  order.forEach((key, index) => {
    const node = sorted.find((n) => n.node_key === key);
    if (!node) {
      return;
    }

    const hasPosition = node.position.x != null && node.position.y != null;
    layouts.push({
      node,
      x: hasPosition ? (node.position.x as number) : 48 + index * (NODE_WIDTH + H_GAP),
      y: hasPosition ? (node.position.y as number) : 120 + (index % 2) * (NODE_HEIGHT + V_GAP),
    });
  });

  return layouts;
}

function topologicalKeys(nodes: WorkflowNodeDto[], edges: WorkflowEdgeDto[]): string[] {
  const keys = nodes.map((n) => n.node_key);
  const inDegree = Object.fromEntries(keys.map((k) => [k, 0]));
  const adj: Record<string, string[]> = {};

  for (const edge of edges) {
    adj[edge.from_node_key] = adj[edge.from_node_key] ?? [];
    adj[edge.from_node_key].push(edge.to_node_key);
    if (inDegree[edge.to_node_key] !== undefined) {
      inDegree[edge.to_node_key]++;
    }
  }

  const queue = keys.filter((k) => inDegree[k] === 0);
  const out: string[] = [];

  while (queue.length) {
    const k = queue.shift()!;
    out.push(k);
    for (const next of adj[k] ?? []) {
      inDegree[next]--;
      if (inDegree[next] === 0) {
        queue.push(next);
      }
    }
  }

  return out.length ? out : keys;
}

export const CANVAS_NODE_SIZE = { width: NODE_WIDTH, height: NODE_HEIGHT };

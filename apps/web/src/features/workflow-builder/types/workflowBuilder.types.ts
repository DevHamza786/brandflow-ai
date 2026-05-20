export type WorkflowNodeType =
  | 'agent'
  | 'delay'
  | 'condition'
  | 'optimization'
  | 'autonomous'
  | 'coordination'
  | 'human_gate';

export interface WorkflowBlueprintDto {
  id: string;
  workspace_id: string;
  slug: string;
  name: string;
  status: string;
  version: number;
  is_active: boolean;
  blueprint_type: string;
  config: Record<string, unknown>;
}

export interface WorkflowNodeDto {
  id: string;
  workspace_id: string;
  workflow_blueprint_id: string;
  node_key: string;
  node_type: WorkflowNodeType;
  label: string | null;
  config: Record<string, unknown>;
  position: { x?: number; y?: number };
  sort_order: number;
}

export interface WorkflowEdgeDto {
  id: string;
  from_node_key: string;
  to_node_key: string;
  edge_type: string;
  condition: Record<string, unknown> | null;
}

export interface WorkflowGraphPayload {
  blueprint: WorkflowBlueprintDto;
  nodes: WorkflowNodeDto[];
  edges: WorkflowEdgeDto[];
}

export interface CanvasNodeLayout {
  node: WorkflowNodeDto;
  x: number;
  y: number;
}

export interface ValidationResult {
  valid: boolean;
  errors: string[];
  warnings: string[];
}

export interface ExecuteBlueprintResult {
  blueprint_id: string;
  workflow_run_id: string | null;
  nodes_executed: number;
  executed_node_keys: string[];
  skipped_node_keys: string[];
  failed_node_keys: string[];
  trace_id: string;
}

export interface WorkflowBuilderUiState {
  selectedNodeKey: string | null;
  panel: 'config' | 'validation' | 'execution' | 'history';
  showPreview: boolean;
}

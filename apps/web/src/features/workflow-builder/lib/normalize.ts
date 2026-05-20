import { asArray, asRecord } from '@/shared/api/normalize';
import type {
  WorkflowBlueprintDto,
  WorkflowEdgeDto,
  WorkflowGraphPayload,
  WorkflowNodeDto,
  WorkflowNodeType,
  ValidationResult,
  ExecuteBlueprintResult,
} from '@/features/workflow-builder/types/workflowBuilder.types';

export function normalizeBlueprint(raw: Record<string, unknown>): WorkflowBlueprintDto {
  return {
    id: str(raw.id),
    workspace_id: str(raw.workspace_id),
    slug: str(raw.slug),
    name: str(raw.name),
    status: str(raw.status),
    version: num(raw.version),
    is_active: Boolean(raw.is_active),
    blueprint_type: str(raw.blueprint_type),
    config: asRecord(raw.config),
  };
}

export function normalizeNode(raw: Record<string, unknown>): WorkflowNodeDto {
  const pos = asRecord(raw.position);

  return {
    id: str(raw.id),
    workspace_id: str(raw.workspace_id),
    workflow_blueprint_id: str(raw.workflow_blueprint_id),
    node_key: str(raw.node_key),
    node_type: str(raw.node_type) as WorkflowNodeType,
    label: raw.label != null ? str(raw.label) : null,
    config: asRecord(raw.config),
    position: { x: num(pos.x), y: num(pos.y) },
    sort_order: num(raw.sort_order),
  };
}

export function normalizeEdge(raw: Record<string, unknown>): WorkflowEdgeDto {
  return {
    id: str(raw.id),
    from_node_key: str(raw.from_node_key),
    to_node_key: str(raw.to_node_key),
    edge_type: str(raw.edge_type),
    condition: raw.condition != null ? asRecord(raw.condition) : null,
  };
}

export function normalizeGraphPayload(data: unknown): WorkflowGraphPayload | null {
  const root = asRecord(data);
  const bp = asRecord(root.blueprint);
  if (!bp.id) {
    return null;
  }

  return {
    blueprint: normalizeBlueprint(bp),
    nodes: asArray<Record<string, unknown>>(root.nodes).map(normalizeNode),
    edges: asArray<Record<string, unknown>>(root.edges).map(normalizeEdge),
  };
}

export function normalizeBlueprintsList(data: unknown): WorkflowBlueprintDto[] {
  const root = asRecord(data);
  return asArray<Record<string, unknown>>(root.blueprints).map(normalizeBlueprint);
}

export function normalizeValidation(data: unknown): ValidationResult {
  const root = asRecord(data);
  return {
    valid: Boolean(root.valid),
    errors: asArray<string>(root.errors),
    warnings: asArray<string>(root.warnings),
  };
}

export function normalizeExecution(data: unknown): ExecuteBlueprintResult {
  const root = asRecord(data);
  return {
    blueprint_id: str(root.blueprint_id),
    workflow_run_id: rawStr(root.workflow_run_id),
    nodes_executed: num(root.nodes_executed),
    executed_node_keys: asArray<string>(root.executed_node_keys),
    skipped_node_keys: asArray<string>(root.skipped_node_keys),
    failed_node_keys: asArray<string>(root.failed_node_keys),
    trace_id: str(root.trace_id),
  };
}

function str(v: unknown): string {
  return typeof v === 'string' ? v : '';
}

function rawStr(v: unknown): string | null {
  return typeof v === 'string' && v !== '' ? v : null;
}

function num(v: unknown): number {
  return typeof v === 'number' ? v : Number(v) || 0;
}

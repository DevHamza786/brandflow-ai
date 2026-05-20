import { apiGet, apiPost } from '@/shared/api/client';
import {
  normalizeBlueprintsList,
  normalizeExecution,
  normalizeGraphPayload,
  normalizeValidation,
} from '@/features/workflow-builder/lib/normalize';
import type {
  ExecuteBlueprintResult,
  ValidationResult,
  WorkflowBlueprintDto,
  WorkflowGraphPayload,
} from '@/features/workflow-builder/types/workflowBuilder.types';

export async function fetchWorkflowBlueprints(): Promise<WorkflowBlueprintDto[]> {
  const data = await apiGet<unknown>('/workflow-builder/blueprints');
  return normalizeBlueprintsList(data);
}

export async function fetchWorkflowBlueprint(
  blueprintId: string,
): Promise<WorkflowGraphPayload | null> {
  const data = await apiGet<unknown>(`/workflow-builder/blueprints/${blueprintId}`);
  return normalizeGraphPayload(data);
}

export async function validateWorkflowBlueprint(
  blueprintId: string,
): Promise<ValidationResult> {
  const data = await apiGet<unknown>(`/workflow-builder/blueprints/${blueprintId}/validate`);
  return normalizeValidation(data);
}

export async function executeWorkflowBlueprint(
  blueprintId?: string,
): Promise<ExecuteBlueprintResult> {
  const path = blueprintId
    ? `/workflow-builder/blueprints/${blueprintId}/execute`
    : '/workflow-builder/execute';
  const data = await apiPost<unknown>(path, {});
  return normalizeExecution(data);
}

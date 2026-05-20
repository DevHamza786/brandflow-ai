import { useMemo, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  executeWorkflowBlueprint,
  fetchWorkflowBlueprint,
  fetchWorkflowBlueprints,
  validateWorkflowBlueprint,
} from '@/features/workflow-builder/api/workflowBuilder.api';
import { layoutWorkflowNodes } from '@/features/workflow-builder/lib/layoutNodes';
import { workflowBuilderKeys } from '@/features/workflow-builder/hooks/workflowBuilderQueryKeys';
import type { WorkflowBuilderUiState } from '@/features/workflow-builder/types/workflowBuilder.types';

const DEFAULT_UI: WorkflowBuilderUiState = {
  selectedNodeKey: null,
  panel: 'config',
  showPreview: false,
};

export function useWorkflowBuilder() {
  const queryClient = useQueryClient();
  const [ui, setUi] = useState<WorkflowBuilderUiState>(DEFAULT_UI);

  const blueprintsQuery = useQuery({
    queryKey: workflowBuilderKeys.blueprints(),
    queryFn: fetchWorkflowBlueprints,
    staleTime: 60_000,
  });

  const activeBlueprint = blueprintsQuery.data?.[0] ?? null;

  const graphQuery = useQuery({
    queryKey: workflowBuilderKeys.graph(activeBlueprint?.id ?? ''),
    queryFn: () => fetchWorkflowBlueprint(activeBlueprint!.id),
    enabled: Boolean(activeBlueprint?.id),
    staleTime: 30_000,
  });

  const validationQuery = useQuery({
    queryKey: workflowBuilderKeys.validation(activeBlueprint?.id ?? ''),
    queryFn: () => validateWorkflowBlueprint(activeBlueprint!.id),
    enabled: Boolean(activeBlueprint?.id) && ui.panel === 'validation',
    staleTime: 15_000,
  });

  const canvasLayouts = useMemo(() => {
    if (!graphQuery.data) {
      return [];
    }
    return layoutWorkflowNodes(graphQuery.data.nodes, graphQuery.data.edges);
  }, [graphQuery.data]);

  const selectedNode = useMemo(() => {
    if (!graphQuery.data || !ui.selectedNodeKey) {
      return null;
    }
    return graphQuery.data.nodes.find((n) => n.node_key === ui.selectedNodeKey) ?? null;
  }, [graphQuery.data, ui.selectedNodeKey]);

  const executeMutation = useMutation({
    mutationFn: () => executeWorkflowBlueprint(activeBlueprint?.id),
    onSuccess: () => {
      setUi((s) => ({ ...s, panel: 'execution', showPreview: true }));
      void queryClient.invalidateQueries({ queryKey: workflowBuilderKeys.all });
    },
  });

  return {
    ui,
    setUi,
    blueprintsQuery,
    graphQuery,
    validationQuery,
    activeBlueprint,
    canvasLayouts,
    selectedNode,
    executeMutation,
    isInitialLoad: blueprintsQuery.isLoading,
    isEmpty: blueprintsQuery.isSuccess && (blueprintsQuery.data?.length ?? 0) === 0,
  };
}

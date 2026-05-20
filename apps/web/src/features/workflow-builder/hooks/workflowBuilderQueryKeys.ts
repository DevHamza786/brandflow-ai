export const workflowBuilderKeys = {
  all: ['workflow-builder'] as const,
  blueprints: () => [...workflowBuilderKeys.all, 'blueprints'] as const,
  graph: (id: string) => [...workflowBuilderKeys.all, 'graph', id] as const,
  validation: (id: string) => [...workflowBuilderKeys.all, 'validation', id] as const,
};

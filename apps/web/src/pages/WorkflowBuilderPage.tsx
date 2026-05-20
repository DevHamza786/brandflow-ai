import { useWorkflowBuilder } from '@/features/workflow-builder/hooks/useWorkflowBuilder';
import { WorkflowCanvas } from '@/features/workflow-builder/components/canvas/WorkflowCanvas';
import { NodeConfigPanel } from '@/features/workflow-builder/components/panels/NodeConfigPanel';
import { ValidationPanel } from '@/features/workflow-builder/components/panels/ValidationPanel';
import { ExecutionPreviewPanel } from '@/features/workflow-builder/components/panels/ExecutionPreviewPanel';
import { WorkflowHistoryPanel } from '@/features/workflow-builder/components/panels/WorkflowHistoryPanel';
import { WorkflowAnalyticsBadge } from '@/features/workflow-builder/components/indicators/WorkflowAnalyticsBadge';
import { WorkflowOptimizationBadge } from '@/features/workflow-builder/components/indicators/WorkflowOptimizationBadge';
import { MultiAgentIndicator } from '@/features/workflow-builder/components/indicators/MultiAgentIndicator';
import { WorkflowBuilderSkeleton } from '@/features/workflow-builder/components/feedback/WorkflowBuilderSkeleton';
import { WorkflowBuilderEmptyState } from '@/features/workflow-builder/components/feedback/WorkflowBuilderEmptyState';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { Button } from '@/shared/components/ui/Button';
import { cn } from '@/shared/lib/cn';

export function WorkflowBuilderPage() {
  const {
    ui,
    setUi,
    blueprintsQuery,
    graphQuery,
    validationQuery,
    activeBlueprint,
    canvasLayouts,
    selectedNode,
    executeMutation,
    isInitialLoad,
    isEmpty,
  } = useWorkflowBuilder();

  if (isInitialLoad) {
    return (
      <div className="mx-auto w-full max-w-[90rem] animate-fade-up">
        <WorkflowBuilderSkeleton />
      </div>
    );
  }

  if (blueprintsQuery.isError) {
    return (
      <ErrorState
        error={
          blueprintsQuery.error instanceof Error
            ? blueprintsQuery.error.message
            : 'Failed to load workflows'
        }
        onRetry={() => void blueprintsQuery.refetch()}
      />
    );
  }

  if (isEmpty) {
    return (
      <WorkflowBuilderEmptyState
        onRetry={() => void blueprintsQuery.refetch()}
      />
    );
  }

  const graph = graphQuery.data;

  return (
    <div className="mx-auto w-full max-w-[90rem] space-y-6 animate-fade-up">
      <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p className="text-xs font-medium uppercase tracking-[0.2em] text-accent">
            Workflow Builder
          </p>
          <h1 className="mt-2 text-3xl font-semibold tracking-tight text-white">
            {activeBlueprint?.name ?? 'AI Workflow'}
          </h1>
          <p className="mt-2 max-w-2xl text-sm text-slate-400">
            Node-based orchestration for multi-agent, optimization, and autonomous flows — visualization
            foundation for future no-code editing.
          </p>
          <div className="mt-4 flex flex-wrap gap-2">
            <MultiAgentIndicator />
            <WorkflowAnalyticsBadge />
            <WorkflowOptimizationBadge />
          </div>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button
            variant="secondary"
            onClick={() => setUi((s) => ({ ...s, panel: 'validation' }))}
          >
            Validate
          </Button>
          <Button
            disabled={executeMutation.isPending || !activeBlueprint}
            onClick={() => executeMutation.mutate()}
          >
            {executeMutation.isPending ? 'Running…' : 'Preview execution'}
          </Button>
        </div>
      </header>

      {graphQuery.isError ? (
        <ErrorState
          error={
            graphQuery.error instanceof Error
              ? graphQuery.error.message
              : 'Failed to load workflow graph'
          }
          onRetry={() => void graphQuery.refetch()}
        />
      ) : graph ? (
        <WorkflowCanvas
          layouts={canvasLayouts}
          edges={graph.edges}
          selectedNodeKey={ui.selectedNodeKey}
          onSelectNode={(key) =>
            setUi((s) => ({ ...s, selectedNodeKey: key, panel: 'config' }))
          }
        />
      ) : (
        <WorkflowBuilderSkeleton />
      )}

      <div className="flex gap-2 border-b border-border pb-2">
        {(['config', 'validation', 'execution', 'history'] as const).map((tab) => (
          <button
            key={tab}
            type="button"
            className={cn(
              'rounded-lg px-3 py-1.5 text-sm font-medium capitalize transition-colors',
              ui.panel === tab
                ? 'bg-accent/15 text-accent'
                : 'text-slate-500 hover:text-slate-300',
            )}
            onClick={() => setUi((s) => ({ ...s, panel: tab }))}
          >
            {tab}
          </button>
        ))}
      </div>

      <div className="grid gap-4 lg:grid-cols-3">
        <div className="lg:col-span-2">
          {ui.panel === 'config' ? <NodeConfigPanel node={selectedNode} /> : null}
          {ui.panel === 'validation' ? (
            <ValidationPanel
              result={validationQuery.data}
              isLoading={validationQuery.isLoading}
            />
          ) : null}
          {ui.panel === 'execution' ? (
            <ExecutionPreviewPanel
              result={executeMutation.data}
              isRunning={executeMutation.isPending}
            />
          ) : null}
          {ui.panel === 'history' ? (
            <WorkflowHistoryPanel blueprint={activeBlueprint} />
          ) : null}
        </div>
        <div className="space-y-4">
          <NodeConfigPanel node={selectedNode} />
          <WorkflowHistoryPanel blueprint={activeBlueprint} />
        </div>
      </div>
    </div>
  );
}

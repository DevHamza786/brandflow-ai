import { Button } from '@/shared/components/ui/Button';

interface Props {
  onRetry?: () => void;
}

export function WorkflowBuilderEmptyState({ onRetry }: Props) {
  return (
    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border px-8 py-16 text-center">
      <p className="text-lg font-medium text-slate-200">No workflow blueprints yet</p>
      <p className="mt-2 max-w-md text-sm text-slate-400">
        The API will seed a default multi-agent blueprint on first load. Refresh to pull the graph.
      </p>
      {onRetry ? (
        <Button className="mt-6" variant="secondary" onClick={onRetry}>
          Refresh blueprints
        </Button>
      ) : null}
    </div>
  );
}

import { EmptyState } from '@/shared/components/feedback/EmptyState';
import { Button } from '@/shared/components/ui/Button';

type Props = { onRun?: () => void; running?: boolean };

export function AutonomousEmptyState({ onRun, running }: Props) {
  return (
    <div className="rounded-xl border border-dashed border-sky-500/30 bg-sky-500/5 px-6 py-12">
      <EmptyState
        title="Start autonomous intelligence"
        description="Run an execution cycle to evaluate posting time, content selection, and composite publish decisions — with confidence gates (no auto-publish in this release)."
      />
      {onRun && (
        <div className="mt-6 flex justify-center">
          <Button onClick={onRun} loading={running}>
            Run evaluation cycle
          </Button>
        </div>
      )}
    </div>
  );
}

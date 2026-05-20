import { EmptyState } from '@/shared/components/feedback/EmptyState';
import { Button } from '@/shared/components/ui/Button';

type Props = {
  onRunCycle?: () => void;
  running?: boolean;
};

export function OptimizationEmptyState({ onRunCycle, running }: Props) {
  return (
    <div className="rounded-xl border border-dashed border-emerald-500/30 bg-emerald-500/5 px-6 py-12">
      <EmptyState
        title="Start your optimization loop"
        description="Collect post performance via analytics, then run an optimization cycle. The dashboard will surface hook, timing, CTA, and audience-fit intelligence with recommendation sync."
      />
      {onRunCycle && (
        <div className="mt-6 flex justify-center">
          <Button onClick={onRunCycle} loading={running}>
            Run first optimization cycle
          </Button>
        </div>
      )}
    </div>
  );
}

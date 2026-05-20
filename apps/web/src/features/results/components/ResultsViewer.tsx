import { HookVariantsList } from '@/features/results/components/HookVariantsList';
import { ResultsMetadata } from '@/features/results/components/ResultsMetadata';
import { ScoreBreakdown } from '@/features/results/components/ScoreBreakdown';
import { SuggestionsPanel } from '@/features/results/components/SuggestionsPanel';
import type { HookResultsViewModel } from '@/features/results/types/results.types';
import { EmptyState } from '@/shared/components/feedback/EmptyState';
import { cn } from '@/shared/lib/cn';

type Props = {
  viewModel: HookResultsViewModel;
  className?: string;
};

export function ResultsViewer({ viewModel, className }: Props) {
  const { variants, dimensions, suggestions, metadata, timestamps, status, overallScore, agentRunId } =
    viewModel;

  if (!viewModel.hasDisplayableResults && status === 'completed') {
    return (
      <EmptyState
        title="No hook outputs"
        description="The workflow completed but returned no variants or scores. Try generating again."
      />
    );
  }

  return (
    <div className={cn('space-y-8 animate-fade-up', className)}>
      <ResultsMetadata
        status={status}
        metadata={metadata}
        timestamps={timestamps}
        overallScore={overallScore}
        agentRunId={agentRunId}
      />

      <div className="grid gap-8 xl:grid-cols-[1fr_320px]">
        <div className="space-y-8 min-w-0">
          <HookVariantsList variants={variants} />
        </div>
        <aside className="space-y-6 xl:sticky xl:top-6 xl:self-start">
          <ScoreBreakdown dimensions={dimensions} />
          <SuggestionsPanel suggestions={suggestions} />
        </aside>
      </div>
    </div>
  );
}

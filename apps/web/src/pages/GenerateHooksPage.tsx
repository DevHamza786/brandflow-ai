import { useState } from 'react';
import { GeneratedHooksPanel } from '@/features/hooks/components/GeneratedHooksPanel';
import { GenerateHooksForm } from '@/features/hooks/components/GenerateHooksForm';

export function GenerateHooksPage() {
  const [activeRunId, setActiveRunId] = useState<string | null>(null);

  return (
    <div className="space-y-8">
      <header className="max-w-3xl">
        <p className="text-xs font-medium uppercase tracking-widest text-accent">Hook Lab</p>
        <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
          Generate Hooks
        </h1>
        <p className="mt-3 text-sm leading-relaxed text-slate-400">
          Dispatch an async AI workflow to score your opening lines and produce ranked hook variants.
          Progress updates automatically while the queue runs — results appear beside the form when ready.
        </p>
      </header>

      <div className="grid gap-8 lg:grid-cols-2 lg:items-start lg:gap-10">
        <div className="min-w-0">
          <GenerateHooksForm onRunQueued={setActiveRunId} />
        </div>
        <aside className="min-w-0 lg:sticky lg:top-24">
          <GeneratedHooksPanel agentRunId={activeRunId} />
        </aside>
      </div>
    </div>
  );
}
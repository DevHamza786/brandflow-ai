import { Link } from 'react-router-dom';
import { Button } from '@/shared/components/ui/Button';

export function CompetitorEmptyState() {
  return (
    <div className="rounded-2xl border border-dashed border-violet-500/30 bg-violet-500/5 px-8 py-14 text-center">
      <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-500/15 text-2xl text-violet-300">
        ◆
      </div>
      <h2 className="mt-6 text-lg font-semibold text-white">No competitors tracked yet</h2>
      <p className="mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-400">
        Add competitors via the API (<code className="font-mono text-xs">POST /api/v1/competitors</code>
        ), then ingest snapshots with post payloads. Scraping UI comes later — intelligence is ready
        for manual and simulated data today.
      </p>
      <div className="mt-8 flex flex-wrap justify-center gap-3">
        <Link to="/analytics">
          <Button variant="secondary">Your analytics</Button>
        </Link>
        <Link to="/generate">
          <Button variant="primary">Hook Lab</Button>
        </Link>
      </div>
    </div>
  );
}

export function CompetitorNoSnapshotState({ name }: { name: string | null }) {
  return (
    <div className="rounded-xl border border-dashed border-border px-6 py-10 text-center">
      <p className="text-sm font-medium text-slate-300">
        No snapshot data for {name ?? 'this competitor'}
      </p>
      <p className="mt-2 text-xs text-slate-500">
        Ingest via <code className="font-mono">POST /api/v1/competitors/:id/snapshots</code> to unlock
        charts and pattern analysis.
      </p>
    </div>
  );
}

import { Link } from 'react-router-dom';
import { Button } from '@/shared/components/ui/Button';

export function AnalyticsEmptyState() {
  return (
    <div className="rounded-2xl border border-dashed border-accent/25 bg-accent/5 px-8 py-14 text-center">
      <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-accent/15 text-2xl">
        ◈
      </div>
      <h2 className="mt-6 text-lg font-semibold text-white">Analytics intelligence is warming up</h2>
      <p className="mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-400">
        There is no performance data for this workspace in the selected range. Publish LinkedIn posts,
        let metrics sync, or score hooks in Hook Lab — snapshots will appear here automatically.
      </p>
      <div className="mt-8 flex flex-wrap justify-center gap-3">
        <Link to="/generate">
          <Button variant="primary">Open Hook Lab</Button>
        </Link>
        <Link to="/integrations/posts">
          <Button variant="secondary">Publishing activity</Button>
        </Link>
      </div>
      <p className="mt-6 text-xs text-slate-600">
        Future: recommendation engine and multi-platform metrics plug into the same dashboard API.
      </p>
    </div>
  );
}

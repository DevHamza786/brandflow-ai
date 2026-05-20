import { Spinner } from '@/shared/components/ui/Spinner';

type Props = {
  status: 'idle' | 'pending' | 'saved' | 'error';
  error?: string | null;
  memoryVersion?: number;
};

export function SaveStatusBadge({ status, error, memoryVersion }: Props) {
  return (
    <div
      className="flex items-center gap-2 text-xs"
      role="status"
      aria-live="polite"
    >
      {status === 'pending' && (
        <>
          <Spinner className="h-3.5 w-3.5" />
          <span className="text-slate-400">Saving…</span>
        </>
      )}
      {status === 'saved' && (
        <span className="text-emerald-400">All changes saved</span>
      )}
      {status === 'error' && (
        <span className="text-red-400">{error ?? 'Save failed'}</span>
      )}
      {status === 'idle' && memoryVersion !== undefined && (
        <span className="text-slate-500">Memory v{memoryVersion}</span>
      )}
    </div>
  );
}

import { Spinner } from '@/shared/components/ui/Spinner';
import { cn } from '@/shared/lib/cn';

export function LoadingState({
  message = 'Loading…',
  className,
}: {
  message?: string;
  className?: string;
}) {
  return (
    <div className={cn('flex flex-col items-center justify-center gap-3 py-16', className)}>
      <Spinner />
      <p className="text-sm text-slate-400">{message}</p>
    </div>
  );
}

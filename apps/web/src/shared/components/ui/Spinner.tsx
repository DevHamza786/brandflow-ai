import { cn } from '@/shared/lib/cn';

export function Spinner({ className }: { className?: string }) {
  return (
    <div
      className={cn(
        'h-8 w-8 animate-spin rounded-full border-2 border-accent/30 border-t-accent',
        className,
      )}
      role="status"
      aria-label="Loading"
    />
  );
}

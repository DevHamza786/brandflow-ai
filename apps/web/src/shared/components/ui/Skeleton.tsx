import { cn } from '@/shared/lib/cn';

type Props = {
  className?: string;
};

export function Skeleton({ className }: Props) {
  return (
    <div
      className={cn(
        'animate-shimmer rounded-lg bg-gradient-to-r from-surface-overlay via-surface-raised to-surface-overlay bg-[length:200%_100%]',
        className,
      )}
      aria-hidden
    />
  );
}

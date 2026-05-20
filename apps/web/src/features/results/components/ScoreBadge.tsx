import { SCORE_TIER_STYLES, scoreTier } from '@/features/results/lib/scoreTier';
import { cn } from '@/shared/lib/cn';

type Props = {
  score: number;
  size?: 'sm' | 'md' | 'lg';
  className?: string;
};

const sizeClasses = {
  sm: 'px-2 py-0.5 text-xs',
  md: 'px-2.5 py-1 text-sm',
  lg: 'px-3 py-1.5 text-base font-semibold',
};

export function ScoreBadge({ score, size = 'md', className }: Props) {
  const tier = scoreTier(score);
  const display = Number.isInteger(score) ? score : score.toFixed(1);

  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full border font-mono tabular-nums',
        SCORE_TIER_STYLES[tier],
        sizeClasses[size],
        className,
      )}
    >
      {display}
    </span>
  );
}

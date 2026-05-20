import { CopyButton } from '@/features/results/components/CopyButton';
import { ScoreBadge } from '@/features/results/components/ScoreBadge';
import type { RankedHookVariant } from '@/features/results/types/results.types';
import { dimensionLabel } from '@/features/results/constants/dimension-labels';
import { Badge } from '@/shared/components/ui/Badge';
import { Card, CardBody } from '@/shared/components/ui/Card';
import { cn } from '@/shared/lib/cn';

type Props = {
  variant: RankedHookVariant;
  index: number;
};

export function HookResultCard({ variant, index }: Props) {
  const dimensionEntries = Object.entries(variant.dimensions ?? {}).filter(
    ([, v]) => typeof v === 'number' && v > 0,
  );

  return (
    <Card
      className={cn(
        'relative overflow-hidden transition-all duration-300 animate-fade-up',
        variant.isBest
          ? 'border-accent/50 bg-accent/[0.06] shadow-glow ring-1 ring-accent/30'
          : 'border-border/70 hover:border-border',
      )}
      style={{ animationDelay: `${index * 60}ms` }}
    >
      {variant.isBest && (
        <div className="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-accent to-transparent" />
      )}

      <CardBody className="space-y-4">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div className="flex flex-wrap items-center gap-2">
            {variant.isBest ? (
              <Badge className="border-accent/40 bg-accent/20 text-accent">Best hook</Badge>
            ) : (
              <span className="text-xs font-medium text-slate-500">#{variant.rank}</span>
            )}
            {variant.experiment_variant && (
              <Badge className="border-border bg-surface-overlay text-slate-400">
                {variant.experiment_variant}
              </Badge>
            )}
          </div>
          <ScoreBadge score={variant.overall} size="lg" />
        </div>

        <p className="text-base leading-relaxed text-slate-100">{variant.text}</p>

        {dimensionEntries.length > 0 && (
          <div className="flex flex-wrap gap-2 border-t border-border/50 pt-3">
            {dimensionEntries.map(([key, value]) => (
              <span
                key={key}
                className="rounded-md bg-surface-overlay px-2 py-1 text-xs text-slate-400"
              >
                <span className="text-slate-500">{dimensionLabel(key)}:</span>{' '}
                <span className="font-mono text-slate-300">{Number(value).toFixed(0)}</span>
              </span>
            ))}
          </div>
        )}

        <div className="flex justify-end border-t border-border/40 pt-3">
          <CopyButton text={variant.text} label="Copy hook" />
        </div>
      </CardBody>
    </Card>
  );
}

import { HookResultCard } from '@/features/results/components/HookResultCard';
import type { RankedHookVariant } from '@/features/results/types/results.types';

type Props = {
  variants: RankedHookVariant[];
};

export function HookVariantsList({ variants }: Props) {
  if (variants.length === 0) return null;

  return (
    <section className="space-y-4" aria-label="Hook variants">
      <div>
        <h2 className="text-lg font-semibold text-white">Generated hooks</h2>
        <p className="mt-1 text-sm text-slate-500">
          Ranked by overall score — highest first
        </p>
      </div>
      <ul className="grid gap-4 lg:grid-cols-2">
        {variants.map((variant, index) => (
          <li key={variant.id} className="list-none">
            <HookResultCard variant={variant} index={index} />
          </li>
        ))}
      </ul>
    </section>
  );
}

import type { HookVariant } from '@/shared/types/api';
import type { RankedHookVariant } from '@/features/results/types/results.types';

function variantId(text: string, index: number): string {
  const slug = text.slice(0, 48).replace(/\s+/g, '-').toLowerCase();
  return `${slug || 'variant'}-${index}`;
}

function safeOverall(value: unknown): number {
  if (typeof value === 'number' && Number.isFinite(value)) return value;
  const n = Number(value);
  return Number.isFinite(n) ? n : 0;
}

/** Parses and sorts variants by overall score (desc). Marks rank 1 as best. */
export function rankHookVariants(raw: unknown[]): RankedHookVariant[] {
  const parsed: HookVariant[] = [];

  for (const item of raw) {
    if (!item || typeof item !== 'object') continue;
    const o = item as Record<string, unknown>;
    const text = typeof o.text === 'string' ? o.text.trim() : '';
    if (!text) continue;

    const dimensions =
      o.dimensions && typeof o.dimensions === 'object' && !Array.isArray(o.dimensions)
        ? (o.dimensions as HookVariant['dimensions'])
        : {};

    parsed.push({
      text,
      overall: safeOverall(o.overall),
      dimensions,
      experiment_variant:
        typeof o.experiment_variant === 'string' ? o.experiment_variant : null,
    });
  }

  const sorted = [...parsed].sort((a, b) => b.overall - a.overall);

  return sorted.map((variant, index) => ({
    ...variant,
    id: variantId(variant.text, index),
    rank: index + 1,
    isBest: index === 0 && sorted.length > 0,
  }));
}

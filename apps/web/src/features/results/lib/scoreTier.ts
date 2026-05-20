export type ScoreTier = 'excellent' | 'strong' | 'moderate' | 'low';

export function scoreTier(value: number): ScoreTier {
  if (value >= 85) return 'excellent';
  if (value >= 70) return 'strong';
  if (value >= 50) return 'moderate';
  return 'low';
}

export const SCORE_TIER_STYLES: Record<ScoreTier, string> = {
  excellent: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
  strong: 'bg-accent/15 text-accent border-accent/40',
  moderate: 'bg-amber-500/15 text-amber-200 border-amber-500/35',
  low: 'bg-slate-500/15 text-slate-400 border-slate-500/30',
};

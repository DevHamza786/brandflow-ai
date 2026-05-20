/** Human labels for hook score dimensions (analytics + UI). */
export const DIMENSION_LABELS: Record<string, string> = {
  curiosity_gap: 'Curiosity gap',
  curiosityGap: 'Curiosity gap',
  specificity: 'Specificity',
  clarity: 'Clarity',
  audience_fit: 'Audience fit',
  audienceFit: 'Audience fit',
};

export function dimensionLabel(key: string): string {
  return DIMENSION_LABELS[key] ?? key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

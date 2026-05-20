export function formatCompact(n: number): string {
  if (n >= 1_000_000) {
    return `${(n / 1_000_000).toFixed(1)}M`;
  }
  if (n >= 10_000) {
    return `${(n / 1_000).toFixed(1)}k`;
  }
  if (n >= 1_000) {
    return `${(n / 1_000).toFixed(1)}k`;
  }
  return String(Math.round(n));
}

export function formatPercent(value: number | null | undefined, digits = 1): string {
  if (value == null || Number.isNaN(value)) {
    return '—';
  }
  return `${(value * 100).toFixed(digits)}%`;
}

export function formatEngagementRate(value: number | null | undefined): string {
  if (value == null) {
    return '—';
  }
  return formatPercent(value, 2);
}

export function formatDeltaPct(delta: number | null | undefined): string {
  if (delta == null) {
    return '—';
  }
  const sign = delta > 0 ? '+' : '';
  return `${sign}${delta.toFixed(1)}%`;
}

export function shortDate(isoDate: string): string {
  const d = new Date(isoDate.includes('T') ? isoDate : `${isoDate}T12:00:00`);
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

export function hourLabel(hour: number): string {
  const h = hour % 24;
  if (h === 0) {
    return '12a';
  }
  if (h === 12) {
    return '12p';
  }
  return h < 12 ? `${h}a` : `${h - 12}p`;
}

import type {
  CompetitorBenchmark,
  CompetitorIntelligenceReport,
  CompetitorPostRow,
  CompetitorSnapshotView,
  CompetitorSummary,
  CompetitorTrends,
  HookPatternInsight,
  HookStyleRow,
} from '@/features/competitors/types/dashboard';

function str(v: unknown): string {
  return typeof v === 'string' ? v : '';
}

function num(v: unknown): number {
  return typeof v === 'number' && !Number.isNaN(v) ? v : 0;
}

function numOrNull(v: unknown): number | null {
  return typeof v === 'number' && !Number.isNaN(v) ? v : null;
}

function engagementRate(post: Record<string, unknown>): number {
  const impressions = num(post.impressions);
  const interactions =
    num(post.likes) + num(post.comments) + num(post.reposts) + num(post.saves);
  if (impressions <= 0) {
    return interactions > 0 ? Math.min(1, interactions / 1000) : 0;
  }
  return interactions / impressions;
}

export function normalizeCompetitor(raw: Record<string, unknown>): CompetitorSummary {
  return {
    id: str(raw.id),
    workspace_id: str(raw.workspace_id ?? raw.workspaceId),
    linkedin_url: str(raw.linkedin_url ?? raw.linkedinUrl),
    name: (raw.name as string | null) ?? null,
    labels: Array.isArray(raw.labels) ? (raw.labels as string[]) : [],
    intelligence_score: numOrNull(raw.intelligence_score ?? raw.intelligenceScore),
    last_analyzed_at: str(raw.last_analyzed_at ?? raw.lastAnalyzedAt) || null,
    is_active: raw.is_active !== false && raw.isActive !== false,
  };
}

function normalizeBenchmark(raw: Record<string, unknown>): CompetitorBenchmark {
  return {
    workspace_posts_observed: num(raw.workspace_posts_observed),
    workspace_avg_engagement_rate: num(raw.workspace_avg_engagement_rate),
    competitor_avg_engagement_rate: num(raw.competitor_avg_engagement_rate),
    competitor_posts_observed: num(raw.competitor_posts_observed),
    delta_pct: numOrNull(raw.delta_pct),
    competitor_ahead: Boolean(raw.competitor_ahead),
  };
}

function normalizeSnapshot(raw: Record<string, unknown>): CompetitorSnapshotView {
  const hookRaw = (raw.hook_patterns ?? raw.hookPatterns ?? {}) as Record<string, unknown>;
  const styles = Array.isArray(hookRaw.styles)
    ? (hookRaw.styles as Record<string, unknown>[]).map(
        (s): HookStyleRow => ({
          style: str(s.style),
          label: str(s.label),
          sample_count: num(s.sample_count),
          avg_engagement_rate: num(s.avg_engagement_rate),
          uplift_pct_vs_snapshot: num(s.uplift_pct_vs_snapshot),
        }),
      )
    : [];

  const insights = Array.isArray(hookRaw.insights)
    ? (hookRaw.insights as Record<string, unknown>[]).map(
        (i): HookPatternInsight => ({
          kind: str(i.kind),
          summary: str(i.summary),
          best_style: i.best_style != null ? str(i.best_style) : undefined,
          worst_style: i.worst_style != null ? str(i.worst_style) : undefined,
          gap_pct: numOrNull(i.gap_pct) ?? undefined,
        }),
      )
    : [];

  const cadenceRaw = (raw.posting_cadence ?? raw.postingCadence ?? {}) as Record<string, unknown>;
  const ctaRaw = (raw.cta_patterns ?? raw.ctaPatterns ?? {}) as Record<string, unknown>;
  const trendRaw = (raw.trend_summary ?? raw.trendSummary ?? {}) as Record<string, unknown>;

  return {
    id: str(raw.id),
    captured_at: str(raw.captured_at ?? raw.capturedAt),
    posts_count: num(raw.posts_count ?? raw.postsCount),
    avg_engagement_rate: numOrNull(raw.avg_engagement_rate ?? raw.avgEngagementRate),
    posts_per_week: numOrNull(raw.posts_per_week ?? raw.postsPerWeek),
    intelligence_score: numOrNull(raw.intelligence_score ?? raw.intelligenceScore),
    engagement_metrics: (raw.engagement_metrics ?? raw.engagementMetrics ?? {}) as Record<
      string,
      unknown
    >,
    hook_patterns: {
      baseline_engagement_rate: numOrNull(hookRaw.baseline_engagement_rate) ?? undefined,
      styles,
      insights,
      dominant_style: hookRaw.dominant_style != null ? str(hookRaw.dominant_style) : null,
    },
    posting_cadence: {
      posts_per_week: numOrNull(cadenceRaw.posts_per_week) ?? undefined,
      hour_histogram: Array.isArray(cadenceRaw.hour_histogram)
        ? (cadenceRaw.hour_histogram as { hour: number; post_count: number }[])
        : [],
      active_days: numOrNull(cadenceRaw.active_days) ?? undefined,
    },
    cta_patterns: {
      top_ctas: Array.isArray(ctaRaw.top_ctas)
        ? (ctaRaw.top_ctas as { cta: string; count: number }[])
        : [],
    },
    trend_summary: normalizeTrends(trendRaw),
    payload: (raw.payload ?? {}) as { posts?: unknown[] },
  };
}

function normalizeTrends(raw: Record<string, unknown>): CompetitorTrends {
  const benchmarkRaw = raw.benchmark;
  return {
    status: str(raw.status) || 'unknown',
    engagement_rate_delta_pct: numOrNull(raw.engagement_rate_delta_pct),
    posts_per_week_delta: numOrNull(raw.posts_per_week_delta),
    intelligence_score_delta: numOrNull(raw.intelligence_score_delta) ?? undefined,
    previous_captured_at: str(raw.previous_captured_at) || undefined,
    benchmark:
      benchmarkRaw && typeof benchmarkRaw === 'object'
        ? normalizeBenchmark(benchmarkRaw as Record<string, unknown>)
        : undefined,
  };
}

export function normalizeIntelligenceReport(raw: Record<string, unknown>): CompetitorIntelligenceReport {
  const competitor = normalizeCompetitor(
    (raw.competitor ?? {}) as Record<string, unknown>,
  );
  const snapRaw = raw.latest_snapshot ?? raw.latestSnapshot;
  const latest =
    snapRaw && typeof snapRaw === 'object'
      ? normalizeSnapshot(snapRaw as Record<string, unknown>)
      : null;

  const benchmarkTop = (raw.benchmark ?? {}) as Record<string, unknown>;
  const trends = normalizeTrends((raw.trends ?? {}) as Record<string, unknown>);
  const benchmark =
    Object.keys(benchmarkTop).length > 0
      ? normalizeBenchmark(benchmarkTop)
      : trends.benchmark ?? {
          workspace_posts_observed: 0,
          workspace_avg_engagement_rate: 0,
          competitor_avg_engagement_rate: 0,
          competitor_posts_observed: 0,
          delta_pct: null,
          competitor_ahead: false,
        };

  const hints = Array.isArray(raw.recommendation_hints ?? raw.recommendationHints)
    ? (raw.recommendation_hints as { type: string; text: string }[])
    : [];

  const insights = Array.isArray(raw.hook_pattern_insights ?? raw.hookPatternInsights)
    ? (raw.hook_pattern_insights as HookPatternInsight[])
    : latest?.hook_patterns.insights ?? [];

  return {
    competitor,
    latest_snapshot: latest,
    hook_pattern_insights: insights,
    benchmark,
    trends,
    intelligence_score: numOrNull(raw.intelligence_score ?? raw.intelligenceScore),
    recommendation_hints: hints,
  };
}

export function extractTopPosts(snapshot: CompetitorSnapshotView | null, limit = 8): CompetitorPostRow[] {
  if (!snapshot?.payload?.posts || !Array.isArray(snapshot.payload.posts)) {
    return [];
  }

  const rows: CompetitorPostRow[] = [];
  for (const item of snapshot.payload.posts) {
    if (!item || typeof item !== 'object') {
      continue;
    }
    const p = item as Record<string, unknown>;
    rows.push({
      post_id: str(p.post_id ?? p.id),
      hook_text: str(p.hook_text ?? p.hook ?? ''),
      published_at: p.published_at != null ? str(p.published_at) : null,
      impressions: num(p.impressions),
      likes: num(p.likes),
      comments: num(p.comments),
      reposts: num(p.reposts),
      saves: num(p.saves),
      engagement_rate: engagementRate(p),
      cta_text: p.cta_text != null ? str(p.cta_text) : null,
    });
  }

  return rows.sort((a, b) => b.engagement_rate - a.engagement_rate).slice(0, limit);
}

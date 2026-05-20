/** Competitor intelligence dashboard types (normalized snake_case). */

export type CompetitorSummary = {
  id: string;
  workspace_id: string;
  linkedin_url: string;
  name: string | null;
  labels: string[];
  intelligence_score: number | null;
  last_analyzed_at: string | null;
  is_active: boolean;
};

export type HookStyleRow = {
  style: string;
  label: string;
  sample_count: number;
  avg_engagement_rate: number;
  uplift_pct_vs_snapshot: number;
};

export type HookPatternInsight = {
  kind: string;
  summary: string;
  best_style?: string;
  worst_style?: string;
  gap_pct?: number;
};

export type CompetitorPostRow = {
  post_id: string;
  hook_text: string;
  published_at: string | null;
  impressions: number;
  likes: number;
  comments: number;
  reposts: number;
  saves: number;
  engagement_rate: number;
  cta_text: string | null;
};

export type CompetitorBenchmark = {
  workspace_posts_observed: number;
  workspace_avg_engagement_rate: number;
  competitor_avg_engagement_rate: number;
  competitor_posts_observed: number;
  delta_pct: number | null;
  competitor_ahead: boolean;
};

export type CompetitorTrends = {
  status: string;
  engagement_rate_delta_pct?: number | null;
  posts_per_week_delta?: number | null;
  intelligence_score_delta?: number;
  previous_captured_at?: string;
  benchmark?: CompetitorBenchmark;
};

export type CompetitorSnapshotView = {
  id: string;
  captured_at: string;
  posts_count: number;
  avg_engagement_rate: number | null;
  posts_per_week: number | null;
  intelligence_score: number | null;
  engagement_metrics: Record<string, unknown>;
  hook_patterns: {
    baseline_engagement_rate?: number;
    styles?: HookStyleRow[];
    insights?: HookPatternInsight[];
    dominant_style?: string | null;
  };
  posting_cadence: {
    posts_per_week?: number;
    hour_histogram?: { hour: number; post_count: number }[];
    active_days?: number;
  };
  cta_patterns: {
    top_ctas?: { cta: string; count: number }[];
  };
  trend_summary: CompetitorTrends;
  payload: { posts?: unknown[] };
};

export type CompetitorIntelligenceReport = {
  competitor: CompetitorSummary;
  latest_snapshot: CompetitorSnapshotView | null;
  hook_pattern_insights: HookPatternInsight[];
  benchmark: CompetitorBenchmark;
  trends: CompetitorTrends;
  intelligence_score: number | null;
  recommendation_hints: { type: string; text: string }[];
};

export type CompetitorRecommendation = {
  id: string;
  type: string;
  title: string;
  summary: string;
  score: number;
  confidence: number | null;
  source: string;
  generated_at: string;
  evidence?: Record<string, unknown>;
  personalization_context?: Record<string, unknown>;
};

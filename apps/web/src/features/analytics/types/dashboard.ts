/** Dashboard read model — mirrors `GET /api/v1/analytics/dashboard`. */

export type AnalyticsDatePreset = '7d' | '30d' | '90d';

export type DashboardFilterState = {
  preset: AnalyticsDatePreset | 'custom';
  from: string | null;
  to: string | null;
};

export type AnalyticsDashboardRange = {
  from: string;
  to: string;
  preset: string | null;
  label: string;
};

export type AnalyticsDashboardKpis = {
  impressions: number;
  likes: number;
  comments: number;
  reposts: number;
  saves: number;
  posts_observed: number;
  engagement_rate_avg: number | null;
  normalized_engagement_avg: number | null;
  hook_score_avg: number | null;
};

export type EngagementSeriesPoint = {
  date: string;
  impressions: number;
  likes: number;
  comments: number;
  reposts: number;
  saves: number;
  posts: number;
  engagement_rate: number | null;
};

export type ScoreTrendPoint = {
  date: string;
  avg_normalized: number | null;
  avg_hook_score: number | null;
};

export type PostingFrequencyPoint = {
  date: string;
  posts: number;
};

export type PostingTimePoint = {
  hour: number;
  sample_count: number;
  avg_normalized: number;
};

export type TopHookRow = {
  id: string;
  entity_id: string;
  hook_text: string | null;
  normalized: number | null;
  hook_score: number | null;
  overall_lab_score: number | null;
  impressions: number;
  likes: number;
  comments: number;
  observed_at: string;
};

export type AudienceOverview = {
  total_interactions: number;
  interaction_mix: {
    likes: number;
    comments: number;
    reposts: number;
    saves: number;
  };
  avg_impressions_per_post: number;
};

export type AnalyticsComparison = {
  previous_range: { from: string; to: string };
  engagement_rate_delta: number | null;
  impressions_delta: number | null;
  posts_observed_delta: number | null;
};

export type AnalyticsDashboardDto = {
  range: AnalyticsDashboardRange;
  kpis: AnalyticsDashboardKpis;
  engagement_series: EngagementSeriesPoint[];
  score_trend: ScoreTrendPoint[];
  posting_frequency: PostingFrequencyPoint[];
  posting_time: PostingTimePoint[];
  top_hooks: TopHookRow[];
  audience_overview: AudienceOverview;
  comparison: AnalyticsComparison;
};

export type AnalyticsDashboardQueryParams = {
  preset?: AnalyticsDatePreset;
  from?: string;
  to?: string;
};

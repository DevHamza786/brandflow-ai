import { apiGet } from '@/shared/api/client';
import {
  normalizeCompetitor,
  normalizeIntelligenceReport,
} from '@/features/competitors/lib/normalize';
import type {
  CompetitorIntelligenceReport,
  CompetitorRecommendation,
  CompetitorSummary,
} from '@/features/competitors/types/dashboard';

export async function fetchCompetitors(): Promise<CompetitorSummary[]> {
  const data = await apiGet<{ competitors: Record<string, unknown>[] }>('/competitors');
  return (data.competitors ?? []).map((c) => normalizeCompetitor(c));
}

export async function fetchCompetitorIntelligence(
  competitorId: string,
): Promise<CompetitorIntelligenceReport> {
  const data = await apiGet<Record<string, unknown>>(`/competitors/${competitorId}`);
  return normalizeIntelligenceReport(data);
}

export async function fetchCompetitorRecommendations(
  competitorId: string,
): Promise<CompetitorRecommendation[]> {
  const data = await apiGet<{ recommendations: Record<string, unknown>[] }>('/recommendations');
  const rows = data.recommendations ?? [];

  return rows
    .filter((r) => {
      const ctx = (r.personalization_context ?? {}) as Record<string, unknown>;
      const source = str(r.source);
      return (
        source === 'competitor_intelligence' &&
        str(ctx.competitor_id) === competitorId
      );
    })
    .map(
      (r): CompetitorRecommendation => ({
        id: str(r.id),
        type: str(r.type),
        title: str(r.title),
        summary: str(r.summary),
        score: typeof r.score === 'number' ? r.score : 0,
        confidence: typeof r.confidence === 'number' ? r.confidence : null,
        source: str(r.source),
        generated_at: str(r.generated_at),
        evidence: r.evidence as Record<string, unknown> | undefined,
        personalization_context: r.personalization_context as Record<string, unknown> | undefined,
      }),
    );
}

function str(v: unknown): string {
  return typeof v === 'string' ? v : '';
}

import { useEffect, useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { fetchCompetitorIntelligence, fetchCompetitors, fetchCompetitorRecommendations } from '@/features/competitors/api/competitors.api';
import { competitorKeys } from '@/features/competitors/hooks/competitorQueryKeys';
import { extractTopPosts } from '@/features/competitors/lib/normalize';

export function useCompetitorDashboard() {
  const [searchParams, setSearchParams] = useSearchParams();

  const listQuery = useQuery({
    queryKey: competitorKeys.list(),
    queryFn: fetchCompetitors,
    staleTime: 60_000,
  });

  const competitors = listQuery.data ?? [];

  const selectedId = useMemo(() => {
    const fromUrl = searchParams.get('competitor');
    if (fromUrl && competitors.some((c) => c.id === fromUrl)) {
      return fromUrl;
    }
    return competitors[0]?.id ?? null;
  }, [searchParams, competitors]);

  const setSelectedId = (id: string) => {
    setSearchParams({ competitor: id }, { replace: true });
  };

  useEffect(() => {
    if (!searchParams.get('competitor') && competitors[0]?.id) {
      setSearchParams({ competitor: competitors[0].id }, { replace: true });
    }
  }, [competitors, searchParams, setSearchParams]);

  const intelligenceQuery = useQuery({
    queryKey: competitorKeys.intelligence(selectedId ?? ''),
    queryFn: () => fetchCompetitorIntelligence(selectedId!),
    enabled: Boolean(selectedId),
    staleTime: 60_000,
  });

  const recommendationsQuery = useQuery({
    queryKey: competitorKeys.recommendations(selectedId ?? ''),
    queryFn: () => fetchCompetitorRecommendations(selectedId!),
    enabled: Boolean(selectedId),
    staleTime: 60_000,
  });

  const topPosts = useMemo(
    () => extractTopPosts(intelligenceQuery.data?.latest_snapshot ?? null),
    [intelligenceQuery.data],
  );

  const hasSnapshots = (intelligenceQuery.data?.latest_snapshot?.posts_count ?? 0) > 0;

  return {
    competitors,
    selectedId,
    setSelectedId,
    listQuery,
    intelligenceQuery,
    recommendationsQuery,
    report: intelligenceQuery.data,
    recommendations: recommendationsQuery.data ?? [],
    topPosts,
    hasSnapshots,
  };
}

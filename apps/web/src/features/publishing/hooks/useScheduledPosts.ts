import { useQuery } from '@tanstack/react-query';
import { apiGet } from '@/shared/api/client';
import { env } from '@/shared/config/env';

export type ScheduledPostRow = {
  id: string;
  status: string;
  scheduled_for: string | null;
  publish_at: string;
  published_at: string | null;
  provider_post_id: string | null;
  linkedin_urn: string | null;
  content: string | null;
  content_preview: string | null;
  generated_output_id: string | null;
  attempt_count: number;
  error_details: Record<string, unknown> | null;
  metadata: Record<string, unknown>;
};

type ListResponse = {
  scheduled_posts: ScheduledPostRow[];
  in_flight: boolean;
};

export function useScheduledPostsList() {
  return useQuery({
    queryKey: ['scheduled-posts', env.workspaceId],
    queryFn: async () => apiGet<ListResponse>('/scheduled-posts?limit=50'),
    refetchInterval: (query) => {
      const d = query.state.data;
      if (d?.in_flight) {
        return env.pollIntervalMs;
      }
      return false;
    },
  });
}

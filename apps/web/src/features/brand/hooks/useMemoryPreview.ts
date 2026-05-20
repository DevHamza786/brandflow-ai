import { useQuery } from '@tanstack/react-query';
import { brandProfileKeys, fetchMemoryPreview } from '@/features/brand/api/brand-profile.api';
import { trackBrandEvent } from '@/features/brand/lib/analytics';

export function useMemoryPreview(
  profileId: string | undefined,
  query: string,
  enabled = true,
) {
  return useQuery({
    queryKey: brandProfileKeys.memoryPreview(profileId ?? '', query),
    queryFn: async () => {
      const preview = await fetchMemoryPreview(profileId!, { query });
      trackBrandEvent('memory_preview_viewed', {
        profile_id: profileId,
        chars: preview.compact_section_chars,
      });
      return preview;
    },
    enabled: Boolean(profileId) && enabled && query.trim().length > 3,
    staleTime: 10_000,
  });
}

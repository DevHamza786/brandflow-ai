import { useQuery } from '@tanstack/react-query';
import { brandProfileKeys, fetchPrimaryBrandProfile } from '@/features/brand/api/brand-profile.api';
import { trackBrandEvent } from '@/features/brand/lib/analytics';

export function useBrandProfile() {
  return useQuery({
    queryKey: brandProfileKeys.primary(),
    queryFn: async () => {
      const profile = await fetchPrimaryBrandProfile();
      trackBrandEvent('brand_profile_viewed', { profile_id: profile.id });
      return profile;
    },
    staleTime: 30_000,
  });
}

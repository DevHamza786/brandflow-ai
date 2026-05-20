import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  brandProfileKeys,
  createWritingSample,
  deleteWritingSample,
  fetchWritingSamples,
  updateWritingSample,
} from '@/features/brand/api/brand-profile.api';
import { trackBrandEvent } from '@/features/brand/lib/analytics';
import type { WritingSampleSourceType } from '@/features/brand/types/brand-profile.types';

export function useWritingSamples(profileId: string | undefined) {
  return useQuery({
    queryKey: brandProfileKeys.samples(profileId ?? ''),
    queryFn: () => fetchWritingSamples(profileId!),
    enabled: Boolean(profileId),
    staleTime: 15_000,
  });
}

export function useWritingSampleMutations(profileId: string | undefined) {
  const queryClient = useQueryClient();
  const key = brandProfileKeys.samples(profileId ?? '');

  const invalidate = () => {
    void queryClient.invalidateQueries({ queryKey: key });
    void queryClient.invalidateQueries({ queryKey: brandProfileKeys.primary() });
  };

  const create = useMutation({
    mutationFn: (body: { content: string; source_type?: WritingSampleSourceType }) =>
      createWritingSample(profileId!, body),
    onSuccess: () => {
      invalidate();
      trackBrandEvent('writing_sample_added', { profile_id: profileId });
    },
  });

  const update = useMutation({
    mutationFn: ({
      sampleId,
      body,
    }: {
      sampleId: string;
      body: { content: string; reextract_style?: boolean };
    }) => updateWritingSample(sampleId, body),
    onSuccess: () => {
      invalidate();
      trackBrandEvent('writing_sample_updated', { profile_id: profileId });
    },
  });

  const remove = useMutation({
    mutationFn: (sampleId: string) => deleteWritingSample(sampleId),
    onMutate: async (sampleId) => {
      await queryClient.cancelQueries({ queryKey: key });
      const previous = queryClient.getQueryData<import('@/features/brand/types/brand-profile.types').WritingSample[]>(key);
      if (previous) {
        queryClient.setQueryData(
          key,
          previous.filter((s) => s.id !== sampleId),
        );
      }
      return { previous };
    },
    onError: (_err, _id, ctx) => {
      if (ctx?.previous) queryClient.setQueryData(key, ctx.previous);
    },
    onSuccess: () => {
      invalidate();
      trackBrandEvent('writing_sample_deleted', { profile_id: profileId });
    },
  });

  return { create, update, remove };
}

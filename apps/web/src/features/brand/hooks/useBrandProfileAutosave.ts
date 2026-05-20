import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useCallback, useEffect, useRef, useState } from 'react';
import {
  brandProfileKeys,
  updateBrandProfile,
} from '@/features/brand/api/brand-profile.api';
import { trackBrandEvent } from '@/features/brand/lib/analytics';
import {
  formValuesToPatch,
  mergeProfilePatch,
  profileToFormValues,
} from '@/features/brand/lib/mapBrandProfile';
import type {
  BrandProfile,
  BrandProfileFormValues,
} from '@/features/brand/types/brand-profile.types';
import { useDebouncedCallback } from '@/shared/hooks/useDebouncedCallback';

type SaveStatus = 'idle' | 'pending' | 'saved' | 'error';

export function useBrandProfileAutosave(profile: BrandProfile | undefined) {
  const queryClient = useQueryClient();
  const baselineRef = useRef<BrandProfileFormValues | null>(null);
  const [values, setValues] = useState<BrandProfileFormValues | null>(null);
  const [saveStatus, setSaveStatus] = useState<SaveStatus>('idle');
  const [saveError, setSaveError] = useState<string | null>(null);

  useEffect(() => {
    if (!profile) return;
    const form = profileToFormValues(profile);
    baselineRef.current = form;
    setValues(form);
    setSaveStatus('idle');
    setSaveError(null);
  }, [profile?.id, profile?.updated_at]);

  const mutation = useMutation({
    mutationFn: async ({
      profileId,
      patch,
    }: {
      profileId: string;
      patch: ReturnType<typeof formValuesToPatch>;
    }) => {
      if (!patch) return null;
      return updateBrandProfile(profileId, patch);
    },
    onMutate: async ({ patch }) => {
      if (!patch) return;
      await queryClient.cancelQueries({ queryKey: brandProfileKeys.primary() });
      const previous = queryClient.getQueryData<BrandProfile>(brandProfileKeys.primary());
      if (previous) {
        queryClient.setQueryData(
          brandProfileKeys.primary(),
          mergeProfilePatch(previous, patch),
        );
      }
      setSaveStatus('pending');
      return { previous };
    },
    onSuccess: (data) => {
      if (data) {
        queryClient.setQueryData(brandProfileKeys.primary(), data);
        baselineRef.current = profileToFormValues(data);
        setValues(profileToFormValues(data));
      }
      setSaveStatus('saved');
      setSaveError(null);
      trackBrandEvent('brand_profile_saved', { profile_id: data?.id });
    },
    onError: (err, _vars, context) => {
      if (context?.previous) {
        queryClient.setQueryData(brandProfileKeys.primary(), context.previous);
      }
      setSaveStatus('error');
      setSaveError(err instanceof Error ? err.message : 'Failed to save');
      trackBrandEvent('brand_profile_save_failed');
    },
  });

  const flushSave = useCallback(() => {
    if (!profile || !values || !baselineRef.current) return;
    const patch = formValuesToPatch(values, baselineRef.current);
    if (!patch) return;
    mutation.mutate({ profileId: profile.id, patch });
  }, [profile, values, mutation]);

  const debouncedSave = useDebouncedCallback(flushSave, 800);

  const updateValues = useCallback(
    (updater: (prev: BrandProfileFormValues) => BrandProfileFormValues) => {
      setValues((prev) => {
        if (!prev) return prev;
        const next = updater(prev);
        return next;
      });
      setSaveStatus('pending');
      debouncedSave();
    },
    [debouncedSave],
  );

  const patchField = useCallback(
    <K extends keyof BrandProfileFormValues>(key: K, value: BrandProfileFormValues[K]) => {
      updateValues((prev) => ({ ...prev, [key]: value }));
    },
    [updateValues],
  );

  return {
    values,
    setValues: updateValues,
    patchField,
    saveStatus,
    saveError,
    flushSave,
    isSaving: mutation.isPending,
  };
}

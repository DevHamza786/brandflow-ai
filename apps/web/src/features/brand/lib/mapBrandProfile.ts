import { emptyBrandFormValues } from '@/features/brand/lib/brand-profile.defaults';
import { normalizeBrandProfile } from '@/features/brand/lib/normalizeBrandProfile';
import type {
  BrandProfile,
  BrandProfileFormValues,
  BrandProfilePatch,
} from '@/features/brand/types/brand-profile.types';

export function profileToFormValues(profile: BrandProfile): BrandProfileFormValues {
  const safe = normalizeBrandProfile(profile);
  return {
    name: safe.name,
    brand_voice: safe.brand_voice,
    tone_profile: {
      ...safe.tone_profile,
      traits: [...safe.tone_profile.traits],
      avoid: [...safe.tone_profile.avoid],
    },
    target_audience: {
      ...safe.target_audience,
      segments: [...safe.target_audience.segments],
      pain_points: [...safe.target_audience.pain_points],
      goals: [...safe.target_audience.goals],
    },
    banned_phrases: [...safe.banned_phrases],
    preferred_ctas: [...safe.preferred_ctas],
    preferred_hook_patterns: [...safe.preferred_hook_patterns],
    style_guidelines: {
      ...safe.style_guidelines,
      do: [...safe.style_guidelines.do],
      dont: [...safe.style_guidelines.dont],
    },
  };
}

export function formValuesToPatch(
  current: BrandProfileFormValues,
  baseline: BrandProfileFormValues,
): BrandProfilePatch | null {
  const patch: BrandProfilePatch = {};

  if (current.name !== baseline.name) patch.name = current.name;
  if (current.brand_voice !== baseline.brand_voice) patch.brand_voice = current.brand_voice;
  if (JSON.stringify(current.tone_profile) !== JSON.stringify(baseline.tone_profile)) {
    patch.tone_profile = current.tone_profile;
  }
  if (JSON.stringify(current.target_audience) !== JSON.stringify(baseline.target_audience)) {
    patch.target_audience = current.target_audience;
  }
  if (JSON.stringify(current.banned_phrases) !== JSON.stringify(baseline.banned_phrases)) {
    patch.banned_phrases = current.banned_phrases;
  }
  if (JSON.stringify(current.preferred_ctas) !== JSON.stringify(baseline.preferred_ctas)) {
    patch.preferred_ctas = current.preferred_ctas;
  }
  if (
    JSON.stringify(current.preferred_hook_patterns) !==
    JSON.stringify(baseline.preferred_hook_patterns)
  ) {
    patch.preferred_hook_patterns = current.preferred_hook_patterns;
  }
  if (JSON.stringify(current.style_guidelines) !== JSON.stringify(baseline.style_guidelines)) {
    patch.style_guidelines = current.style_guidelines;
  }

  return Object.keys(patch).length > 0 ? patch : null;
}

export function mergeProfilePatch(profile: BrandProfile, patch: BrandProfilePatch): BrandProfile {
  const base = normalizeBrandProfile(profile);
  return normalizeBrandProfile({
    ...base,
    ...patch,
    tone_profile: patch.tone_profile
      ? { ...base.tone_profile, ...patch.tone_profile }
      : base.tone_profile,
    target_audience: patch.target_audience
      ? { ...base.target_audience, ...patch.target_audience }
      : base.target_audience,
    style_guidelines: patch.style_guidelines
      ? { ...base.style_guidelines, ...patch.style_guidelines }
      : base.style_guidelines,
    memory_version: base.memory_version + 1,
    updated_at: new Date().toISOString(),
  });
}

export function createEmptyFormFromProfile(profile: BrandProfile | undefined): BrandProfileFormValues {
  return profile ? profileToFormValues(profile) : emptyBrandFormValues();
}

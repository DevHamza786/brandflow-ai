import type { BrandProfileFormValues, ToneProfile } from '@/features/brand/types/brand-profile.types';

export const DEFAULT_TONE: ToneProfile = {
  primary: 'professional',
  traits: [],
  avoid: [],
  formality: null,
  energy: null,
};

export const emptyBrandFormValues = (): BrandProfileFormValues => ({
  name: 'Primary Brand',
  brand_voice: '',
  tone_profile: { ...DEFAULT_TONE, traits: [], avoid: [] },
  target_audience: {
    summary: '',
    segments: [],
    pain_points: [],
    goals: [],
  },
  banned_phrases: [],
  preferred_ctas: [],
  preferred_hook_patterns: [],
  style_guidelines: {
    summary: '',
    do: [],
    dont: [],
    max_hook_length: null,
    use_emojis: null,
  },
});

import { DEFAULT_TONE, emptyBrandFormValues } from '@/features/brand/lib/brand-profile.defaults';
import type {
  AudienceProfile,
  BrandProfile,
  StyleGuidelines,
  ToneProfile,
} from '@/features/brand/types/brand-profile.types';
import { asArray } from '@/shared/api/normalize';
import { isRecord } from '@/shared/api/normalize';

function stringList(value: unknown): string[] {
  return asArray<string>(value).filter((s) => typeof s === 'string' && s.trim() !== '');
}

function normalizeToneProfile(raw: unknown): ToneProfile {
  const data = isRecord(raw) ? raw : {};
  return {
    primary: typeof data.primary === 'string' ? data.primary : DEFAULT_TONE.primary,
    traits: stringList(data.traits),
    avoid: stringList(data.avoid),
    formality: typeof data.formality === 'number' ? data.formality : null,
    energy: typeof data.energy === 'number' ? data.energy : null,
  };
}

function normalizeAudience(raw: unknown): AudienceProfile {
  const data = isRecord(raw) ? raw : {};
  return {
    summary: typeof data.summary === 'string' ? data.summary : '',
    segments: stringList(data.segments),
    pain_points: stringList(data.pain_points ?? data.painPoints),
    goals: stringList(data.goals),
  };
}

function normalizeStyleGuidelines(raw: unknown): StyleGuidelines {
  const data = isRecord(raw) ? raw : {};
  return {
    summary: typeof data.summary === 'string' ? data.summary : '',
    do: stringList(data.do ?? data.doList),
    dont: stringList(data.dont ?? data.dontList),
    max_hook_length:
      typeof data.max_hook_length === 'number'
        ? data.max_hook_length
        : typeof data.maxHookLength === 'number'
          ? data.maxHookLength
          : null,
    use_emojis:
      typeof data.use_emojis === 'boolean'
        ? data.use_emojis
        : typeof data.useEmojis === 'boolean'
          ? data.useEmojis
          : null,
  };
}

/** Coerce API / cache payloads into a safe BrandProfile shape */
export function normalizeBrandProfile(raw: unknown): BrandProfile {
  if (!isRecord(raw)) {
    const empty = emptyBrandFormValues();
    return {
      id: '',
      workspace_id: '',
      name: empty.name,
      brand_voice: empty.brand_voice,
      tone_profile: empty.tone_profile,
      target_audience: empty.target_audience,
      banned_phrases: [],
      preferred_ctas: [],
      preferred_hook_patterns: [],
      style_guidelines: empty.style_guidelines,
      memory_version: 1,
      is_primary: true,
      metadata: {},
      pillars: [],
    };
  }

  return {
    id: String(raw.id ?? ''),
    workspace_id: String(raw.workspace_id ?? raw.workspaceId ?? ''),
    name: typeof raw.name === 'string' ? raw.name : 'Primary Brand',
    brand_voice: typeof raw.brand_voice === 'string' ? raw.brand_voice : '',
    tone_profile: normalizeToneProfile(raw.tone_profile ?? raw.toneProfile),
    target_audience: normalizeAudience(raw.target_audience ?? raw.targetAudience),
    banned_phrases: stringList(raw.banned_phrases ?? raw.bannedPhrases),
    preferred_ctas: stringList(raw.preferred_ctas ?? raw.preferredCtas),
    preferred_hook_patterns: stringList(
      raw.preferred_hook_patterns ?? raw.preferredHookPatterns,
    ),
    style_guidelines: normalizeStyleGuidelines(
      raw.style_guidelines ?? raw.styleGuidelines,
    ),
    memory_version: Number(raw.memory_version ?? raw.memoryVersion ?? 1),
    is_primary: Boolean(raw.is_primary ?? raw.isPrimary ?? true),
    metadata: isRecord(raw.metadata) ? raw.metadata : {},
    pillars: stringList(raw.pillars),
    created_at: typeof raw.created_at === 'string' ? raw.created_at : null,
    updated_at: typeof raw.updated_at === 'string' ? raw.updated_at : null,
  };
}

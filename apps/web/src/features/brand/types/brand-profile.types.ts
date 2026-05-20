/** Brand memory DTOs — snake_case matches API resources */

export type WritingSampleSourceType =
  | 'manual'
  | 'linkedin_post'
  | 'content_import'
  | 'agent_output'
  | 'email';

export interface ToneProfile {
  primary: string;
  traits: string[];
  avoid: string[];
  formality?: number | null;
  energy?: number | null;
}

export interface AudienceProfile {
  summary: string;
  segments: string[];
  pain_points: string[];
  goals: string[];
}

export interface StyleGuidelines {
  summary: string;
  do: string[];
  dont: string[];
  max_hook_length?: number | null;
  use_emojis?: boolean | null;
}

export interface BrandProfile {
  id: string;
  workspace_id: string;
  name: string;
  brand_voice: string;
  tone_profile: ToneProfile;
  target_audience: AudienceProfile;
  banned_phrases: string[];
  preferred_ctas: string[];
  preferred_hook_patterns: string[];
  style_guidelines: StyleGuidelines;
  memory_version: number;
  is_primary: boolean;
  metadata: Record<string, unknown>;
  pillars: string[];
  created_at?: string | null;
  updated_at?: string | null;
}

export interface NormalizedStyleData {
  avg_sentence_length?: number;
  vocabulary_level?: string;
  punctuation_style?: string;
  [key: string]: unknown;
}

export interface WritingSample {
  id: string;
  workspace_id: string;
  brand_profile_id: string | null;
  content: string;
  source_type: WritingSampleSourceType;
  metadata: Record<string, unknown>;
  embedding_ready: boolean;
  normalized_style_data: NormalizedStyleData;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface BrandMemoryPreview {
  compact_brand_section: string;
  compact_section_chars: number;
  banned_phrases: string[];
  preferred_ctas: string[];
  preferred_hook_patterns: string[];
  memory_version: number;
  profile_id: string | null;
  used_fallback: boolean;
  style_signals: Record<string, unknown>;
  personalization_meta: Record<string, unknown>;
  chunk_ids: string[];
  analytics: Record<string, unknown>;
}

/** Form state for settings UI */
export interface BrandProfileFormValues {
  name: string;
  brand_voice: string;
  tone_profile: ToneProfile;
  target_audience: AudienceProfile;
  banned_phrases: string[];
  preferred_ctas: string[];
  preferred_hook_patterns: string[];
  style_guidelines: StyleGuidelines;
}

export type BrandProfilePatch = Partial<{
  name: string;
  brand_voice: string;
  tone_profile: Partial<ToneProfile>;
  target_audience: Partial<AudienceProfile>;
  banned_phrases: string[];
  preferred_ctas: string[];
  preferred_hook_patterns: string[];
  style_guidelines: Partial<StyleGuidelines>;
}>;

export interface BrandProfileListResponse {
  profiles: BrandProfile[];
}

export interface WritingSampleListResponse {
  samples: WritingSample[];
}

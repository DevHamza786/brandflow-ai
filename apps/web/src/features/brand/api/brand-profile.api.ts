import { normalizeBrandProfile } from '@/features/brand/lib/normalizeBrandProfile';
import { apiDelete, apiGet, apiPatch, apiPost } from '@/shared/api/client';
import type {
  BrandMemoryPreview,
  BrandProfile,
  BrandProfileListResponse,
  BrandProfilePatch,
  WritingSample,
  WritingSampleListResponse,
  WritingSampleSourceType,
} from '@/features/brand/types/brand-profile.types';

export const brandProfileKeys = {
  all: ['brand-profiles'] as const,
  primary: () => [...brandProfileKeys.all, 'primary'] as const,
  detail: (id: string) => [...brandProfileKeys.all, id] as const,
  samples: (profileId: string) => [...brandProfileKeys.all, profileId, 'samples'] as const,
  memoryPreview: (profileId: string, query: string) =>
    [...brandProfileKeys.all, profileId, 'preview', query] as const,
};

export async function fetchPrimaryBrandProfile(): Promise<BrandProfile> {
  const raw = await apiGet<BrandProfile>('/brand-profiles/primary');
  return normalizeBrandProfile(raw);
}

export async function fetchBrandProfiles(): Promise<BrandProfile[]> {
  const res = await apiGet<BrandProfileListResponse>('/brand-profiles');
  return res.profiles;
}

export async function fetchBrandProfile(profileId: string): Promise<BrandProfile> {
  return apiGet<BrandProfile>(`/brand-profiles/${profileId}`);
}

export async function updateBrandProfile(
  profileId: string,
  patch: BrandProfilePatch,
): Promise<BrandProfile> {
  const raw = await apiPatch<BrandProfile>(`/brand-profiles/${profileId}`, patch);
  return normalizeBrandProfile(raw);
}

export async function setPrimaryBrandProfile(profileId: string): Promise<BrandProfile> {
  return apiPost<BrandProfile>(`/brand-profiles/${profileId}/primary`);
}

export async function fetchMemoryPreview(
  profileId: string,
  params: { query?: string; target_audience?: string; content_pillar?: string },
): Promise<BrandMemoryPreview> {
  const search = new URLSearchParams();
  if (params.query) search.set('query', params.query);
  if (params.target_audience) search.set('target_audience', params.target_audience);
  if (params.content_pillar) search.set('content_pillar', params.content_pillar);
  const qs = search.toString();

  return apiGet<BrandMemoryPreview>(
    `/brand-profiles/${profileId}/memory-preview${qs ? `?${qs}` : ''}`,
  );
}

export async function fetchWritingSamples(profileId: string): Promise<WritingSample[]> {
  const res = await apiGet<WritingSampleListResponse>(
    `/brand-profiles/${profileId}/writing-samples`,
  );
  return res.samples;
}

export async function createWritingSample(
  profileId: string,
  body: {
    content: string;
    source_type?: WritingSampleSourceType;
    extract_style?: boolean;
  },
): Promise<WritingSample> {
  return apiPost<WritingSample>(`/brand-profiles/${profileId}/writing-samples`, body);
}

export async function updateWritingSample(
  sampleId: string,
  body: {
    content?: string;
    source_type?: WritingSampleSourceType;
    reextract_style?: boolean;
  },
): Promise<WritingSample> {
  return apiPatch<WritingSample>(`/writing-samples/${sampleId}`, body);
}

export async function deleteWritingSample(sampleId: string): Promise<void> {
  await apiDelete<{ deleted: boolean }>(`/writing-samples/${sampleId}`);
}

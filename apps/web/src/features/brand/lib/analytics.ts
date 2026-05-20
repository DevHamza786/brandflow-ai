/**
 * Analytics-ready event helpers — wire to product analytics when available.
 */
export type BrandAnalyticsEvent =
  | 'brand_profile_viewed'
  | 'brand_profile_saved'
  | 'brand_profile_save_failed'
  | 'writing_sample_added'
  | 'writing_sample_updated'
  | 'writing_sample_deleted'
  | 'memory_preview_viewed';

export function trackBrandEvent(
  event: BrandAnalyticsEvent,
  payload: Record<string, unknown> = {},
): void {
  if (import.meta.env.DEV) {
    console.debug(`[brand.analytics] ${event}`, payload);
  }
}

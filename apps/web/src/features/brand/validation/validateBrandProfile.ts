import type { BrandProfileFormValues } from '@/features/brand/types/brand-profile.types';

export type BrandProfileFormErrors = Partial<Record<keyof BrandProfileFormValues | 'root', string>>;

export function validateBrandProfile(values: BrandProfileFormValues): BrandProfileFormErrors {
  const errors: BrandProfileFormErrors = {};

  if (!values.name.trim()) {
    errors.name = 'Profile name is required';
  }

  if (values.brand_voice.length > 4000) {
    errors.brand_voice = 'Brand voice must be under 4000 characters';
  }

  if (!values.target_audience.summary.trim() && values.target_audience.segments.length === 0) {
    errors.target_audience = 'Add an audience summary or at least one segment';
  }

  return errors;
}

export function hasBrandFormErrors(errors: BrandProfileFormErrors): boolean {
  return Object.keys(errors).length > 0;
}

import type { GenerateHooksFormValues } from '@/features/hooks/types/generate-hooks.types';

const UUID_RE =
  /^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export type GenerateHooksFormErrors = Partial<Record<keyof GenerateHooksFormValues, string>>;

export function validateGenerateHooksForm(values: GenerateHooksFormValues): GenerateHooksFormErrors {
  const errors: GenerateHooksFormErrors = {};

  if (!values.contentVersionId.trim()) {
    errors.contentVersionId = 'Content version is required.';
  } else if (!UUID_RE.test(values.contentVersionId.trim())) {
    errors.contentVersionId = 'Enter a valid content version UUID.';
  }

  if (values.topic.length > 4000) {
    errors.topic = 'Topic must be 4000 characters or less.';
  }

  if (values.targetAudience.length > 500) {
    errors.targetAudience = 'Target audience must be 500 characters or less.';
  }

  if (values.contentPillar.length > 255) {
    errors.contentPillar = 'Content pillar must be 255 characters or less.';
  }

  if (values.maxVariants < 1 || values.maxVariants > 10) {
    errors.maxVariants = 'Variants must be between 1 and 10.';
  }

  return errors;
}

export function hasFormErrors(errors: GenerateHooksFormErrors): boolean {
  return Object.keys(errors).length > 0;
}

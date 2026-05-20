import type {
  GenerateHooksFormValues,
  GenerateHooksRequest,
} from '@/features/hooks/types/generate-hooks.types';

/** Maps UI form → API options (backend-validated fields only). */
export function buildGenerateHooksPayload(values: GenerateHooksFormValues): GenerateHooksRequest {
  const topic = values.topic.trim();
  const audience = values.targetAudience.trim();
  const pillar = values.contentPillar.trim();

  const experimentParts = [`tone:${values.tone}`];
  if (topic) {
    experimentParts.push(`topic:${slugify(topic.slice(0, 80))}`);
  }

  return {
    options: {
      max_variants: values.maxVariants,
      ...(audience ? { target_audience: audience } : {}),
      ...(pillar ? { content_pillar: pillar } : {}),
      experiment_id: experimentParts.join('|'),
    },
  };
}

function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
    .slice(0, 60);
}

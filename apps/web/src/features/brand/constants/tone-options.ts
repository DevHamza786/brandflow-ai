export const BRAND_TONE_OPTIONS = [
  { value: 'professional', label: 'Professional' },
  { value: 'conversational', label: 'Conversational' },
  { value: 'bold', label: 'Bold' },
  { value: 'educational', label: 'Educational' },
  { value: 'storytelling', label: 'Storytelling' },
  { value: 'provocative', label: 'Provocative' },
  { value: 'corporate', label: 'Corporate' },
  { value: 'playful', label: 'Playful / Gen-Z' },
] as const;

export const WRITING_SAMPLE_SOURCE_OPTIONS = [
  { value: 'manual', label: 'Manual paste' },
  { value: 'linkedin_post', label: 'LinkedIn post' },
  { value: 'content_import', label: 'Content import' },
  { value: 'agent_output', label: 'Agent output' },
  { value: 'email', label: 'Email' },
] as const;

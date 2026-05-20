import type { HookTone } from '@/features/hooks/types/generate-hooks.types';

export const HOOK_TONES: { value: HookTone; label: string; description: string }[] = [
  { value: 'professional', label: 'Professional', description: 'Clear, credible, executive tone' },
  { value: 'conversational', label: 'Conversational', description: 'Friendly and approachable' },
  { value: 'bold', label: 'Bold', description: 'Direct, confident statements' },
  { value: 'educational', label: 'Educational', description: 'Teach-first, insight-led' },
  { value: 'storytelling', label: 'Storytelling', description: 'Narrative hooks and tension' },
  { value: 'provocative', label: 'Provocative', description: 'Challenge assumptions' },
];

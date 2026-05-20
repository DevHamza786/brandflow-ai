/** Hook generation feature DTOs — aligned with Laravel GenerateHooksRequest */

export type HookTone =
  | 'professional'
  | 'conversational'
  | 'bold'
  | 'educational'
  | 'storytelling'
  | 'provocative';

export type GenerateHooksFormValues = {
  contentVersionId: string;
  topic: string;
  targetAudience: string;
  tone: HookTone;
  contentPillar: string;
  maxVariants: number;
};

export type GenerateHooksOptions = {
  max_variants?: number;
  target_audience?: string;
  content_pillar?: string;
  experiment_id?: string;
};

export type GenerateHooksRequest = {
  options?: GenerateHooksOptions;
};

export type GenerateHooksResponse = {
  agent_run: { id: string; slug: string; status: string };
  workflow_run: { id: string; status: string; workflow_slug?: string };
  was_replayed: boolean;
  poll_url?: string;
  detail_url?: string;
};

import { apiPost } from '@/shared/api/client';
import {
  fetchAgentRunDetail,
  fetchAgentRunResults,
} from '@/features/workflow/api/agent-runs.api';
import type { GenerateHooksPayload, HookGenerationAccept } from '@/shared/types/api';

/** @deprecated Prefer fetchAgentRunDetail from @/features/workflow/api/agent-runs.api */
export const workflowApi = {
  generateHooks(contentVersionId: string, payload: GenerateHooksPayload = {}) {
    return apiPost<HookGenerationAccept>(
      `/content-versions/${contentVersionId}/hooks/generate`,
      payload,
    );
  },

  getAgentRun: fetchAgentRunDetail,
  getResults: fetchAgentRunResults,
};

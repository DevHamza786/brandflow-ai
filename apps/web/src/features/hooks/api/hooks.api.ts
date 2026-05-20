import { apiPost } from '@/shared/api/client';
import type {
  GenerateHooksRequest,
  GenerateHooksResponse,
} from '@/features/hooks/types/generate-hooks.types';
import type { HookGenerationAccept } from '@/shared/types/api';

function mapResponse(raw: HookGenerationAccept): GenerateHooksResponse {
  return {
    agent_run: {
      id: raw.agent_run.id,
      slug: raw.agent_run.slug,
      status: raw.agent_run.status,
    },
    workflow_run: {
      id: raw.workflow_run.id,
      status: raw.workflow_run.status,
      workflow_slug: raw.workflow_run.workflow_slug,
    },
    was_replayed: raw.was_replayed,
    poll_url: raw.poll_url,
    detail_url: raw.detail_url,
  };
}

export async function postGenerateHooks(
  contentVersionId: string,
  body: GenerateHooksRequest,
): Promise<GenerateHooksResponse> {
  const data = await apiPost<HookGenerationAccept>(
    `/content-versions/${contentVersionId}/hooks/generate`,
    body,
  );
  return mapResponse(data);
}

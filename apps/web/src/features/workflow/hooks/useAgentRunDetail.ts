import { useQuery, useQueryClient } from '@tanstack/react-query';
import { queryKeys } from '@/app/queryClient';
import { fetchAgentRunDetail } from '@/features/workflow/api/agent-runs.api';
import { resolveWorkflowPollInterval } from '@/features/workflow/lib/polling';
import { resolveWorkflowStatus } from '@/features/workflow/lib/resolveWorkflowStatus';
import type { AgentRunDetail, AgentRunResults } from '@/shared/types/api';

export function useAgentRunDetail(agentRunId: string | undefined) {
  const queryClient = useQueryClient();

  return useQuery({
    queryKey: queryKeys.agentRun(agentRunId ?? ''),
    queryFn: () => fetchAgentRunDetail(agentRunId!),
    enabled: Boolean(agentRunId),
    refetchInterval: (query) => {
      const detail = query.state.data as AgentRunDetail | undefined;
      const results = queryClient.getQueryData<AgentRunResults>(
        queryKeys.agentResults(agentRunId ?? ''),
      );
      const status = resolveWorkflowStatus(results, detail);
      return resolveWorkflowPollInterval(status);
    },
  });
}

import { useQuery, useQueryClient } from '@tanstack/react-query';
import { queryKeys } from '@/app/queryClient';
import { fetchAgentRunResults } from '@/features/workflow/api/agent-runs.api';
import { resolveWorkflowPollInterval } from '@/features/workflow/lib/polling';
import { resolveWorkflowStatus } from '@/features/workflow/lib/resolveWorkflowStatus';
import type { AgentRunResults } from '@/shared/types/api';

export function useAgentRunResults(agentRunId: string | undefined) {
  const queryClient = useQueryClient();

  return useQuery({
    queryKey: queryKeys.agentResults(agentRunId ?? ''),
    queryFn: () => fetchAgentRunResults(agentRunId!),
    enabled: Boolean(agentRunId),
    refetchInterval: (query) => {
      const results = query.state.data as AgentRunResults | undefined;
      const detail = queryClient.getQueryData<import('@/shared/types/api').AgentRunDetail>(
        queryKeys.agentRun(agentRunId ?? ''),
      );
      const status = resolveWorkflowStatus(results, detail);
      return resolveWorkflowPollInterval(status);
    },
  });
}

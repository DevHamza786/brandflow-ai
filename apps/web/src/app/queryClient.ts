import { QueryClient } from '@tanstack/react-query';
import type { ApiError } from '@/shared/types/api';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: (failureCount, error) => {
        const status = (error as ApiError)?.status;
        if (status && status >= 400 && status < 500) return false;
        return failureCount < 2;
      },
      staleTime: 5_000,
      refetchOnWindowFocus: true,
    },
    mutations: {
      retry: false,
    },
  },
});

export const queryKeys = {
  agentRun: (id: string) => ['agent-run', id] as const,
  agentResults: (id: string) => ['agent-results', id] as const,
};

import { useMutation } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';
import { postGenerateHooks } from '@/features/hooks/api/hooks.api';
import { buildGenerateHooksPayload } from '@/features/hooks/lib/buildGenerateHooksPayload';
import type { GenerateHooksFormValues } from '@/features/hooks/types/generate-hooks.types';
import { useToast } from '@/shared/providers/ToastProvider';
import type { ApiError } from '@/shared/types/api';

export type UseHookGenerationOptions = {
  /** When true, navigate to `/runs/:id` after the run is queued (legacy flow). Default: false — stay on Generate Hooks and show results inline. */
  navigateToRun?: boolean;
  /** Fires as soon as the API accepts the run (before optional navigation). */
  onQueued?: (agentRunId: string) => void;
};

export function useHookGeneration(options: UseHookGenerationOptions = {}) {
  const { navigateToRun = false, onQueued } = options;
  const navigate = useNavigate();
  const toast = useToast();

  return useMutation({
    mutationFn: async (values: GenerateHooksFormValues) => {
      const payload = buildGenerateHooksPayload(values);
      return postGenerateHooks(values.contentVersionId.trim(), payload);
    },
    onMutate: () => {
      toast.push('Dispatching hook generation to queue…', 'info');
    },
    onSuccess: (data) => {
      const runId = data.agent_run.id;
      onQueued?.(runId);
      toast.push(
        navigateToRun
          ? 'Queued — tracking workflow status'
          : 'Queued — results will appear on the right when ready',
        'success',
      );
      if (navigateToRun) {
        navigate(`/runs/${runId}`, { replace: true });
      }
    },
    onError: (error: ApiError) => {
      toast.push(error.message ?? 'Failed to start hook generation', 'error');
    },
  });
}

/** @deprecated Use useHookGeneration */
export { useHookGeneration as useGenerateHooks };

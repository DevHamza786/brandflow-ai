import type { WorkflowProgressStep } from '@/features/workflow/types/workflow.types';

export const WORKFLOW_PROGRESS_STEPS: WorkflowProgressStep[] = [
  {
    id: 'queued',
    label: 'Queued',
    description: 'Waiting for an AI worker slot',
  },
  {
    id: 'running',
    label: 'Running',
    description: 'Agents scoring and generating variants',
  },
  {
    id: 'completed',
    label: 'Completed',
    description: 'Outputs ready to review',
  },
];

export const WORKFLOW_FAILED_STEP: WorkflowProgressStep = {
  id: 'failed',
  label: 'Failed',
  description: 'Workflow stopped with an error',
};

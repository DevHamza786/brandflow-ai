import type { AutonomousFilterState } from '@/features/autonomous/types/dashboard';

export const autonomousKeys = {
  all: ['autonomous'] as const,
  workflows: () => [...autonomousKeys.all, 'workflows'] as const,
  snapshots: (workflowId?: string) =>
    [...autonomousKeys.all, 'snapshots', workflowId ?? 'recent'] as const,
  dashboard: (filters: AutonomousFilterState, workflowId?: string) =>
    [...autonomousKeys.all, 'dashboard', filters, workflowId ?? ''] as const,
};

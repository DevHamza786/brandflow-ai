import type { OptimizationFilterState } from '@/features/optimization/types/dashboard';

export const optimizationKeys = {
  all: ['optimization'] as const,
  loops: () => [...optimizationKeys.all, 'loops'] as const,
  snapshots: (loopId?: string) =>
    [...optimizationKeys.all, 'snapshots', loopId ?? 'recent'] as const,
  recommendations: () => [...optimizationKeys.all, 'recommendations'] as const,
  dashboard: (filters: OptimizationFilterState, loopId?: string) =>
    [...optimizationKeys.all, 'dashboard', filters, loopId ?? ''] as const,
};

import { Spinner } from '@/shared/components/ui/Spinner';
import type { ValidationResult } from '@/features/workflow-builder/types/workflowBuilder.types';

interface Props {
  result: ValidationResult | undefined;
  isLoading: boolean;
}

export function ValidationPanel({ result, isLoading }: Props) {
  if (isLoading) {
    return (
      <div className="flex items-center gap-3 rounded-xl border border-border bg-surface-overlay p-5 text-sm text-slate-400">
        <Spinner className="h-4 w-4" />
        Validating DAG…
      </div>
    );
  }

  if (!result) {
    return null;
  }

  return (
    <div className="rounded-xl border border-border bg-surface-overlay p-5">
      <div className="flex items-center justify-between">
        <p className="text-sm font-medium text-slate-200">Graph validation</p>
        <span
          className={
            result.valid
              ? 'text-xs font-semibold text-emerald-400'
              : 'text-xs font-semibold text-rose-400'
          }
        >
          {result.valid ? 'Valid' : 'Invalid'}
        </span>
      </div>
      {result.errors.length > 0 ? (
        <ul className="mt-3 list-inside list-disc text-xs text-rose-300">
          {result.errors.map((e) => (
            <li key={e}>{e}</li>
          ))}
        </ul>
      ) : null}
      {result.warnings.length > 0 ? (
        <ul className="mt-3 list-inside list-disc text-xs text-amber-300/90">
          {result.warnings.map((w) => (
            <li key={w}>{w}</li>
          ))}
        </ul>
      ) : null}
      {result.valid && result.errors.length === 0 ? (
        <p className="mt-3 text-xs text-slate-500">Ready for execution preview.</p>
      ) : null}
    </div>
  );
}

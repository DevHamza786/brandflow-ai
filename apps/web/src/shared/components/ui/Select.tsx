import { cn } from '@/shared/lib/cn';
import type { SelectHTMLAttributes } from 'react';

type Option = { value: string; label: string };

type Props = SelectHTMLAttributes<HTMLSelectElement> & {
  label?: string;
  hint?: string;
  error?: string;
  options: Option[];
};

export function Select({ className, label, hint, error, options, id, ...props }: Props) {
  const selectId = id ?? label?.toLowerCase().replace(/\s+/g, '-');

  return (
    <label className="block space-y-1.5">
      {label && (
        <span className="text-xs font-medium uppercase tracking-wide text-slate-400">{label}</span>
      )}
      <select
        id={selectId}
        className={cn(
          'w-full rounded-lg border bg-surface-overlay px-3 py-2.5 text-sm text-slate-100',
          'focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent/50',
          error ? 'border-red-500/50' : 'border-border',
          className,
        )}
        {...props}
      >
        {options.map((opt) => (
          <option key={opt.value} value={opt.value} className="bg-surface-raised">
            {opt.label}
          </option>
        ))}
      </select>
      {error && <span className="text-xs text-red-400">{error}</span>}
      {hint && !error && <span className="text-xs text-slate-500">{hint}</span>}
    </label>
  );
}

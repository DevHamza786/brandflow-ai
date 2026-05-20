import { cn } from '@/shared/lib/cn';
import type { InputHTMLAttributes } from 'react';

type Props = InputHTMLAttributes<HTMLInputElement> & {
  label?: string;
  hint?: string;
  error?: string;
};

export function Input({ className, label, hint, error, id, ...props }: Props) {
  const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-');
  return (
    <label className="block space-y-1.5">
      {label && (
        <span className="text-xs font-medium uppercase tracking-wide text-slate-400">{label}</span>
      )}
      <input
        id={inputId}
        className={cn(
          'w-full rounded-lg border border-border bg-surface-overlay px-3 py-2.5 text-sm text-slate-100',
          'placeholder:text-slate-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent/50',
          error && 'border-red-500/60 focus:border-red-500 focus:ring-red-500/40',
          className,
        )}
        {...props}
      />
      {error ? (
        <span className="text-xs text-red-400" role="alert">
          {error}
        </span>
      ) : (
        hint && <span className="text-xs text-slate-500">{hint}</span>
      )}
    </label>
  );
}

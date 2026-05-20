import { cn } from '@/shared/lib/cn';
import type { ButtonHTMLAttributes } from 'react';

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger';

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: Variant;
  loading?: boolean;
};

const variants: Record<Variant, string> = {
  primary:
    'bg-accent text-slate-950 hover:bg-sky-400 shadow-glow border border-accent/50 disabled:opacity-50',
  secondary: 'bg-surface-overlay border border-border text-slate-200 hover:bg-surface-raised',
  ghost: 'text-slate-300 hover:bg-surface-overlay hover:text-white',
  danger: 'bg-red-500/20 text-red-300 border border-red-500/40 hover:bg-red-500/30',
};

export function Button({
  className,
  variant = 'primary',
  loading,
  disabled,
  children,
  ...props
}: Props) {
  return (
    <button
      className={cn(
        'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors',
        variants[variant],
        className,
      )}
      disabled={disabled || loading}
      {...props}
    >
      {loading && (
        <span className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
      )}
      {children}
    </button>
  );
}

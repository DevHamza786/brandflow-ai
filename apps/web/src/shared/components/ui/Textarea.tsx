import { cn } from '@/shared/lib/cn';
import { useCallback, useEffect, useRef, type TextareaHTMLAttributes } from 'react';

type Props = TextareaHTMLAttributes<HTMLTextAreaElement> & {
  label?: string;
  hint?: string;
  error?: string;
  autoSize?: boolean;
  minRows?: number;
  maxRows?: number;
};

export function Textarea({
  className,
  label,
  hint,
  error,
  autoSize = true,
  minRows = 3,
  maxRows = 12,
  id,
  onInput,
  ...props
}: Props) {
  const ref = useRef<HTMLTextAreaElement>(null);
  const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-');

  const resize = useCallback(() => {
    const el = ref.current;
    if (!el || !autoSize) return;

    el.style.height = 'auto';
    const lineHeight = 22;
    const minH = minRows * lineHeight;
    const maxH = maxRows * lineHeight;
    const next = Math.min(Math.max(el.scrollHeight, minH), maxH);
    el.style.height = `${next}px`;
    el.style.overflowY = el.scrollHeight > maxH ? 'auto' : 'hidden';
  }, [autoSize, minRows, maxRows]);

  useEffect(() => {
    resize();
  }, [props.value, resize]);

  return (
    <label className="block space-y-1.5">
      {label && (
        <span className="text-xs font-medium uppercase tracking-wide text-slate-400">{label}</span>
      )}
      <textarea
        ref={ref}
        id={inputId}
        rows={minRows}
        onInput={(e) => {
          onInput?.(e);
          resize();
        }}
        className={cn(
          'w-full resize-none rounded-lg border bg-surface-overlay px-3 py-2.5 text-sm text-slate-100',
          'placeholder:text-slate-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent/50',
          error ? 'border-red-500/50' : 'border-border',
          className,
        )}
        {...props}
      />
      {error && <span className="text-xs text-red-400">{error}</span>}
      {hint && !error && <span className="text-xs text-slate-500">{hint}</span>}
    </label>
  );
}

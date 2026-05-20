import type { ReactNode } from 'react';
import { cn } from '@/shared/lib/cn';

type Props = {
  title: string;
  description?: string;
  children: ReactNode;
  className?: string;
  action?: ReactNode;
};

export function SettingsSection({ title, description, children, className, action }: Props) {
  return (
    <section
      className={cn(
        'animate-fade-up rounded-xl border border-border bg-surface-raised/80 p-5 sm:p-6',
        className,
      )}
    >
      <header className="mb-5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h3 className="text-base font-semibold text-white">{title}</h3>
          {description && <p className="mt-1 max-w-2xl text-sm text-slate-400">{description}</p>}
        </div>
        {action}
      </header>
      {children}
    </section>
  );
}

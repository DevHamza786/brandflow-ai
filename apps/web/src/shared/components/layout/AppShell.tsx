import { NavLink, Outlet } from 'react-router-dom';
import { cn } from '@/shared/lib/cn';

const nav = [
  { to: '/analytics', label: 'Analytics' },
  { to: '/optimization', label: 'Optimization' },
  { to: '/autonomous', label: 'Autonomous' },
  { to: '/workflows', label: 'Workflows' },
  { to: '/competitors', label: 'Competitors' },
  { to: '/brand', label: 'Brand Memory' },
  { to: '/settings/integrations', label: 'LinkedIn' },
  { to: '/integrations/posts', label: 'LinkedIn posts' },
  { to: '/generate', label: 'Generate Hooks' },
];

export function AppShell() {
  return (
    <div className="min-h-screen bg-surface">
      <div className="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(59,158,255,0.08),_transparent_50%)]" />
      <header className="sticky top-0 z-40 border-b border-border/80 bg-surface/90 backdrop-blur-md">
        <div className="mx-auto flex h-14 max-w-[90rem] items-center justify-between px-4 sm:px-6">
          <div className="flex items-center gap-3">
            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/20 text-sm font-bold text-accent">
              BF
            </span>
            <span className="font-semibold tracking-tight text-white">BrandFlow AI</span>
          </div>
          <nav className="flex gap-1">
            {nav.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                className={({ isActive }) =>
                  cn(
                    'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    isActive
                      ? 'bg-accent/15 text-accent'
                      : 'text-slate-400 hover:bg-surface-overlay hover:text-slate-200',
                  )
                }
              >
                {item.label}
              </NavLink>
            ))}
          </nav>
        </div>
      </header>
      <main className="relative mx-auto max-w-[90rem] px-4 py-8 sm:px-6 sm:py-10">
        <Outlet />
      </main>
    </div>
  );
}

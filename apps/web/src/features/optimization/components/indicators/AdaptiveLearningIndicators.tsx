import type { AdaptiveLearningStatus } from '@/features/optimization/types/dashboard';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { cn } from '@/shared/lib/cn';

type Props = {
  status: AdaptiveLearningStatus;
};

export function AdaptiveLearningIndicators({ status }: Props) {
  const items = [
    {
      label: 'ML schema',
      value: `v${status.schema_version}`,
      ok: status.schema_version >= 1,
      hint: 'Portable feature payloads',
    },
    {
      label: 'Bandit arms',
      value: status.active_arms.length > 0 ? status.active_arms.join(', ') : 'Pending',
      ok: status.active_arms.length > 0,
      hint: 'Per-focus experiment arms',
    },
    {
      label: 'Embeddings',
      value: status.embedding_ready ? 'Linked' : 'Ready slot',
      ok: status.embedding_ready,
      hint: 'RAG / similarity future hook',
    },
    {
      label: 'RL policy',
      value: status.rl_ready ? 'Active' : 'Reserved',
      ok: status.rl_ready,
      hint: 'Reinforcement loop placeholder',
    },
    {
      label: 'Experiment slots',
      value: String(status.experiment_slots || '—'),
      ok: status.experiment_slots > 0,
      hint: 'Workflow builder compatibility',
    },
    {
      label: 'Last cycle',
      value: status.last_cycle > 0 ? `#${status.last_cycle}` : '—',
      ok: status.last_cycle > 0,
      hint: 'Self-improvement cadence',
    },
  ];

  return (
    <Card className="border-violet-500/20 bg-gradient-to-br from-violet-500/5 to-transparent">
      <CardHeader>
        <h2 className="text-sm font-medium text-slate-200">Adaptive learning</h2>
        <p className="mt-1 text-xs text-slate-500">
          AI-native indicators — autonomous agents and experiment engines read the same signals
        </p>
      </CardHeader>
      <CardBody>
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((item) => (
            <div
              key={item.label}
              className={cn(
                'rounded-lg border px-3 py-2.5 transition-colors',
                item.ok
                  ? 'border-emerald-500/30 bg-emerald-500/5'
                  : 'border-border/80 bg-surface-overlay/50',
              )}
            >
              <p className="text-[10px] font-medium uppercase tracking-wider text-slate-500">
                {item.label}
              </p>
              <p className="mt-1 truncate text-sm font-medium text-slate-200">{item.value}</p>
              <p className="mt-0.5 text-[10px] text-slate-500">{item.hint}</p>
            </div>
          ))}
        </div>
      </CardBody>
    </Card>
  );
}

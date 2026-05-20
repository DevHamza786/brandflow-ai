import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { cn } from '@/shared/lib/cn';

type Props = {
  adaptive: {
    schema_version: number;
    agent_ready: boolean;
    rl_ready: boolean;
    experiment_slots: number;
  };
};

export function OptimizationLearningIndicators({ adaptive }: Props) {
  const items = [
    { label: 'ML schema', value: `v${adaptive.schema_version}`, ok: true },
    { label: 'Multi-agent', value: adaptive.agent_ready ? 'Ready' : 'Pending', ok: adaptive.agent_ready },
    { label: 'RL policy', value: adaptive.rl_ready ? 'Linked' : 'Reserved', ok: adaptive.rl_ready },
    { label: 'Experiments', value: String(adaptive.experiment_slots || '—'), ok: adaptive.experiment_slots > 0 },
  ];

  return (
    <Card className="border-violet-500/20 bg-gradient-to-br from-violet-500/5 to-transparent">
      <CardHeader>
        <h2 className="text-sm font-medium text-slate-200">Optimization learning</h2>
        <p className="mt-1 text-xs text-slate-500">Feeds from optimization loops — observe → optimize → decide</p>
      </CardHeader>
      <CardBody className="grid gap-2 sm:grid-cols-2">
        {items.map((item) => (
          <div
            key={item.label}
            className={cn(
              'rounded-lg border px-3 py-2',
              item.ok ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-border/80',
            )}
          >
            <p className="text-[10px] uppercase tracking-wider text-slate-500">{item.label}</p>
            <p className="text-sm font-medium text-slate-200">{item.value}</p>
          </div>
        ))}
      </CardBody>
    </Card>
  );
}

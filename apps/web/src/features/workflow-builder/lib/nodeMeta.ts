import type { WorkflowNodeType } from '@/features/workflow-builder/types/workflowBuilder.types';

export interface NodeVisualMeta {
  label: string;
  accent: string;
  icon: string;
  category: 'agent' | 'integration' | 'gate' | 'schedule';
}

const META: Record<WorkflowNodeType, NodeVisualMeta> = {
  coordination: {
    label: 'Multi-Agent Coordination',
    accent: 'text-violet-400',
    icon: '◈',
    category: 'agent',
  },
  agent: {
    label: 'AI Agent',
    accent: 'text-accent',
    icon: '◎',
    category: 'agent',
  },
  optimization: {
    label: 'Optimization Loop',
    accent: 'text-emerald-400',
    icon: '↻',
    category: 'integration',
  },
  autonomous: {
    label: 'Autonomous Engine',
    accent: 'text-amber-400',
    icon: '⚡',
    category: 'integration',
  },
  delay: {
    label: 'Scheduled Delay',
    accent: 'text-slate-400',
    icon: '⏱',
    category: 'schedule',
  },
  condition: {
    label: 'Conditional Branch',
    accent: 'text-cyan-400',
    icon: '◇',
    category: 'gate',
  },
  human_gate: {
    label: 'Human Approval',
    accent: 'text-rose-400',
    icon: '✋',
    category: 'gate',
  },
};

export function nodeMeta(type: WorkflowNodeType): NodeVisualMeta {
  return META[type] ?? META.agent;
}

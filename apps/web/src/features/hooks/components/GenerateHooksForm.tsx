import { useState, type FormEvent } from 'react';
import { Button } from '@/shared/components/ui/Button';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { Input } from '@/shared/components/ui/Input';
import { Select } from '@/shared/components/ui/Select';
import { Textarea } from '@/shared/components/ui/Textarea';
import { env } from '@/shared/config/env';
import { HOOK_TONES } from '@/features/hooks/constants/tones';
import { useHookGeneration } from '@/features/hooks/hooks/useHookGeneration';
import type { GenerateHooksFormValues, HookTone } from '@/features/hooks/types/generate-hooks.types';
import {
  hasFormErrors,
  validateGenerateHooksForm,
  type GenerateHooksFormErrors,
} from '@/features/hooks/validation/validateGenerateHooksForm';
import { cn } from '@/shared/lib/cn';

type GenerateHooksFormProps = {
  /** Called with the new agent run id when the server accepts the job (for inline results). */
  onRunQueued?: (agentRunId: string) => void;
};

const defaultValues = (): GenerateHooksFormValues => ({
  contentVersionId: env.defaultContentVersionId,
  topic: '',
  targetAudience: '',
  tone: 'professional',
  contentPillar: '',
  maxVariants: 3,
});

export function GenerateHooksForm({ onRunQueued }: GenerateHooksFormProps = {}) {
  const [values, setValues] = useState<GenerateHooksFormValues>(defaultValues);
  const [errors, setErrors] = useState<GenerateHooksFormErrors>({});
  const [showAdvanced, setShowAdvanced] = useState(false);

  const mutation = useHookGeneration({
    onQueued: onRunQueued,
  });
  const isSubmitting = mutation.isPending;

  const patch = <K extends keyof GenerateHooksFormValues>(
    key: K,
    value: GenerateHooksFormValues[K],
  ) => {
    setValues((prev) => ({ ...prev, [key]: value }));
    setErrors((prev) => {
      const next = { ...prev };
      delete next[key];
      return next;
    });
  };

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    if (isSubmitting) return;

    const nextErrors = validateGenerateHooksForm(values);
    if (hasFormErrors(nextErrors)) {
      setErrors(nextErrors);
      return;
    }

    mutation.mutate(values);
  };

  const selectedTone = HOOK_TONES.find((t) => t.value === values.tone);

  return (
    <form onSubmit={handleSubmit} className="space-y-6" noValidate>
      {isSubmitting && (
        <div
          className="rounded-lg border border-accent/30 bg-accent/10 px-4 py-3 text-sm text-accent"
          role="status"
          aria-live="polite"
        >
          Queuing your hook generation workflow…
        </div>
      )}

      <Card className={cn('max-w-2xl transition-opacity', isSubmitting && 'opacity-80')}>
        <CardHeader>
          <h2 className="text-sm font-medium text-slate-300">Hook Lab input</h2>
          <p className="mt-1 text-xs text-slate-500">
            Scores opening lines from your content version and generates variants asynchronously.
          </p>
        </CardHeader>
        <CardBody className="space-y-5">
          <Textarea
            label="Content / topic context"
            name="topic"
            value={values.topic}
            onChange={(e) => patch('topic', e.target.value)}
            placeholder="What is this post about? Key angle, offer, or narrative you want the hooks to reflect…"
            hint="Used for workflow metadata; opening lines are read from your saved content version."
            error={errors.topic}
            disabled={isSubmitting}
            minRows={4}
            maxRows={14}
          />

          <div className="grid gap-5 sm:grid-cols-2">
            <Input
              label="Target audience"
              name="targetAudience"
              value={values.targetAudience}
              onChange={(e) => patch('targetAudience', e.target.value)}
              placeholder="B2B founders, RevOps leaders…"
              error={errors.targetAudience}
              disabled={isSubmitting}
            />

            <Select
              label="Tone"
              name="tone"
              value={values.tone}
              onChange={(e) => patch('tone', e.target.value as HookTone)}
              options={HOOK_TONES.map((t) => ({ value: t.value, label: t.label }))}
              hint={selectedTone?.description}
              disabled={isSubmitting}
            />
          </div>

          <Input
            label="Content pillar (optional)"
            name="contentPillar"
            value={values.contentPillar}
            onChange={(e) => patch('contentPillar', e.target.value)}
            placeholder="Thought leadership, product launches, hiring…"
            error={errors.contentPillar}
            disabled={isSubmitting}
          />

          <button
            type="button"
            className="text-xs font-medium text-slate-500 hover:text-slate-300"
            onClick={() => setShowAdvanced((v) => !v)}
            disabled={isSubmitting}
          >
            {showAdvanced ? 'Hide' : 'Show'} advanced options
          </button>

          {showAdvanced && (
            <div className="grid gap-4 rounded-lg border border-border/60 bg-surface-overlay/50 p-4 sm:grid-cols-2">
              <Input
                label="Content version ID"
                value={values.contentVersionId}
                onChange={(e) => patch('contentVersionId', e.target.value)}
                error={errors.contentVersionId}
                disabled={isSubmitting}
                className="sm:col-span-2 font-mono text-xs"
              />
              <Input
                label="Max variants"
                type="number"
                min={1}
                max={10}
                value={values.maxVariants}
                onChange={(e) => patch('maxVariants', Number(e.target.value) || 1)}
                error={errors.maxVariants}
                disabled={isSubmitting}
              />
            </div>
          )}

          <div className="flex flex-col gap-3 border-t border-border pt-5 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-xs text-slate-500">
              Generation runs on the AI queue — hooks appear on the right when ready. You can still open
              the full workflow view from the results panel.
            </p>
            <Button
              type="submit"
              loading={isSubmitting}
              disabled={isSubmitting}
              className="w-full sm:w-auto sm:min-w-[180px]"
            >
              {isSubmitting ? 'Queuing…' : 'Generate hooks'}
            </Button>
          </div>
        </CardBody>
      </Card>
    </form>
  );
}

import { useState } from 'react';
import { WRITING_SAMPLE_SOURCE_OPTIONS } from '@/features/brand/constants/tone-options';
import type { WritingSampleSourceType } from '@/features/brand/types/brand-profile.types';
import { Button } from '@/shared/components/ui/Button';
import { Select } from '@/shared/components/ui/Select';
import { Textarea } from '@/shared/components/ui/Textarea';
import { cn } from '@/shared/lib/cn';

type Props = {
  onSubmit: (payload: { content: string; source_type: WritingSampleSourceType }) => void;
  isSubmitting?: boolean;
  progress?: 'idle' | 'uploading' | 'extracting' | 'done';
};

export function WritingSampleUpload({ onSubmit, isSubmitting, progress = 'idle' }: Props) {
  const [content, setContent] = useState('');
  const [sourceType, setSourceType] = useState<WritingSampleSourceType>('manual');
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = () => {
    const trimmed = content.trim();
    if (trimmed.length < 20) {
      setError('Paste at least 20 characters of sample copy');
      return;
    }
    setError(null);
    onSubmit({ content: trimmed, source_type: sourceType });
    setContent('');
  };

  const progressLabel =
    progress === 'uploading'
      ? 'Saving sample…'
      : progress === 'extracting'
        ? 'Extracting style signals…'
        : progress === 'done'
          ? 'Sample added'
          : null;

  return (
    <div
      className={cn(
        'rounded-lg border border-dashed border-border/80 bg-surface-overlay/50 p-4 transition-colors',
        isSubmitting && 'border-accent/40 bg-accent/5',
      )}
    >
      <div className="mb-3 flex flex-col gap-3 sm:flex-row sm:items-end">
        <div className="flex-1">
          <Select
            label="Source type"
            value={sourceType}
            onChange={(e) => setSourceType(e.target.value as WritingSampleSourceType)}
            disabled={isSubmitting}
            options={[...WRITING_SAMPLE_SOURCE_OPTIONS]}
          />
        </div>
      </div>
      <Textarea
        label="Paste writing sample"
        value={content}
        onChange={(e) => setContent(e.target.value)}
        disabled={isSubmitting}
        error={error ?? undefined}
        placeholder="Paste a LinkedIn post, email, or article excerpt you want the AI to mirror…"
        minRows={4}
      />
      <div className="mt-3 flex items-center justify-between gap-3">
        {progressLabel && (
          <span className="text-xs text-accent" role="status">
            {progressLabel}
          </span>
        )}
        <Button
          type="button"
          onClick={handleSubmit}
          loading={isSubmitting}
          disabled={isSubmitting || content.trim().length < 20}
          className="ml-auto"
        >
          Add sample
        </Button>
      </div>
    </div>
  );
}

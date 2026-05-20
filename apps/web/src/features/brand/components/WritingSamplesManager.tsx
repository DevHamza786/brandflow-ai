import { useState } from 'react';
import { WRITING_SAMPLE_SOURCE_OPTIONS } from '@/features/brand/constants/tone-options';
import { useWritingSampleMutations, useWritingSamples } from '@/features/brand/hooks/useWritingSamples';
import { WritingSampleUpload } from '@/features/brand/components/WritingSampleUpload';
import { SettingsSection } from '@/features/brand/components/settings/SettingsSection';
import type { WritingSample } from '@/features/brand/types/brand-profile.types';
import { Button } from '@/shared/components/ui/Button';
import { Textarea } from '@/shared/components/ui/Textarea';
import { LoadingState } from '@/shared/components/feedback/LoadingState';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { cn } from '@/shared/lib/cn';

type Props = {
  profileId: string | undefined;
};

function sourceLabel(value: string): string {
  return WRITING_SAMPLE_SOURCE_OPTIONS.find((o) => o.value === value)?.label ?? value;
}

function SampleCard({
  sample,
  onSave,
  onDelete,
  isDeleting,
}: {
  sample: WritingSample;
  onSave: (content: string) => void;
  onDelete: () => void;
  isDeleting: boolean;
}) {
  const [editing, setEditing] = useState(false);
  const [draft, setDraft] = useState(sample.content);
  const excerpt = sample.content.length > 220 ? `${sample.content.slice(0, 220)}…` : sample.content;
  const avgLen = sample.normalized_style_data?.avg_sentence_length;

  return (
    <article
      className={cn(
        'rounded-lg border border-border bg-surface-overlay p-4 transition-opacity',
        isDeleting && 'opacity-50',
      )}
    >
      <div className="mb-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
        <span className="rounded-full bg-surface-raised px-2 py-0.5">{sourceLabel(sample.source_type)}</span>
        {sample.embedding_ready && (
          <span className="text-emerald-400">Style extracted</span>
        )}
        {avgLen != null && <span>~{Math.round(Number(avgLen))} words/sentence</span>}
      </div>

      {editing ? (
        <>
          <Textarea
            value={draft}
            onChange={(e) => setDraft(e.target.value)}
            minRows={4}
            autoSize
          />
          <div className="mt-3 flex gap-2">
            <Button
              type="button"
              onClick={() => {
                onSave(draft);
                setEditing(false);
              }}
            >
              Save
            </Button>
            <Button type="button" variant="ghost" onClick={() => setEditing(false)}>
              Cancel
            </Button>
          </div>
        </>
      ) : (
        <>
          <p className="whitespace-pre-wrap text-sm leading-relaxed text-slate-300">{excerpt}</p>
          <div className="mt-3 flex gap-2">
            <Button type="button" variant="secondary" onClick={() => setEditing(true)}>
              Edit
            </Button>
            <Button type="button" variant="danger" onClick={onDelete} loading={isDeleting}>
              Delete
            </Button>
          </div>
        </>
      )}
    </article>
  );
}

export function WritingSamplesManager({ profileId }: Props) {
  const { data: samples, isLoading, isError, error, refetch } = useWritingSamples(profileId);
  const { create, update, remove } = useWritingSampleMutations(profileId);

  const uploadProgress = create.isPending
    ? ('extracting' as const)
    : create.isSuccess
      ? ('done' as const)
      : ('idle' as const);

  return (
    <SettingsSection
      title="Writing samples"
      description="Text samples teach Hook Lab your cadence, sentence length, and voice — vector search ready."
    >
      <WritingSampleUpload
        onSubmit={(body) => create.mutate(body)}
        isSubmitting={create.isPending}
        progress={uploadProgress}
      />

      <div className="mt-6 space-y-3">
        {isLoading && <LoadingState message="Loading samples…" />}
        {isError && (
          <ErrorState
            error={error instanceof Error ? error : 'Could not load samples'}
            onRetry={() => void refetch()}
          />
        )}
        {samples?.length === 0 && !isLoading && (
          <p className="text-sm text-slate-500">No samples yet — add your first above.</p>
        )}
        {samples?.map((sample) => (
          <SampleCard
            key={sample.id}
            sample={sample}
            onSave={(content) =>
              update.mutate({ sampleId: sample.id, body: { content, reextract_style: true } })
            }
            onDelete={() => remove.mutate(sample.id)}
            isDeleting={remove.isPending && remove.variables === sample.id}
          />
        ))}
      </div>
    </SettingsSection>
  );
}

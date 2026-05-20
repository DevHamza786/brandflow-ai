import { useState } from 'react';
import { useMemoryPreview } from '@/features/brand/hooks/useMemoryPreview';
import { SettingsSection } from '@/features/brand/components/settings/SettingsSection';
import { Button } from '@/shared/components/ui/Button';
import { Input } from '@/shared/components/ui/Input';
import { Spinner } from '@/shared/components/ui/Spinner';
import { ErrorState } from '@/shared/components/feedback/ErrorState';

type Props = {
  profileId: string | undefined;
};

export function MemoryPreviewPanel({ profileId }: Props) {
  const [query, setQuery] = useState('LinkedIn hook for AI automation founders');
  const [debouncedQuery, setDebouncedQuery] = useState(query);

  const preview = useMemoryPreview(profileId, debouncedQuery);

  const refresh = () => setDebouncedQuery(query);

  return (
    <SettingsSection
      title="Memory preview"
      description="See the compact brand section Hook Lab injects into prompts — updates after you save."
      action={
        <Button type="button" variant="secondary" onClick={refresh} disabled={preview.isFetching}>
          Refresh preview
        </Button>
      }
    >
      <Input
        label="Sample hook topic"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        onKeyDown={(e) => e.key === 'Enter' && refresh()}
        hint="Press Enter or Refresh to load preview"
      />

      <div className="mt-4 rounded-lg border border-border bg-surface-overlay p-4">
        {preview.isLoading && (
          <div className="flex items-center gap-2 text-sm text-slate-400">
            <Spinner />
            Composing memory context…
          </div>
        )}
        {preview.isError && (
          <ErrorState
            error={preview.error instanceof Error ? preview.error : 'Preview unavailable'}
            onRetry={refresh}
          />
        )}
        {preview.data && (
          <div className="space-y-3 animate-fade-up">
            <div className="flex flex-wrap gap-3 text-xs text-slate-500">
              <span>{preview.data.compact_section_chars} chars</span>
              <span>Memory v{preview.data.memory_version}</span>
              {preview.data.used_fallback && (
                <span className="text-amber-400">Using fallback (no profile)</span>
              )}
            </div>
            <pre className="max-h-64 overflow-auto whitespace-pre-wrap font-mono text-xs leading-relaxed text-slate-300">
              {preview.data.compact_brand_section || '— No compact section (empty profile) —'}
            </pre>
            {preview.data.banned_phrases.length > 0 && (
              <p className="text-xs text-slate-500">
                Banned: {preview.data.banned_phrases.join('; ')}
              </p>
            )}
          </div>
        )}
      </div>
    </SettingsSection>
  );
}

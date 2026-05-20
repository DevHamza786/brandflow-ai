import { Link } from 'react-router-dom';
import { BrandProfileForm } from '@/features/brand/components/BrandProfileForm';
import { SaveStatusBadge } from '@/features/brand/components/settings/SaveStatusBadge';
import { useBrandProfile } from '@/features/brand/hooks/useBrandProfile';
import { useBrandProfileAutosave } from '@/features/brand/hooks/useBrandProfileAutosave';
import {
  hasBrandFormErrors,
  validateBrandProfile,
} from '@/features/brand/validation/validateBrandProfile';
import { Button } from '@/shared/components/ui/Button';
import { LoadingState } from '@/shared/components/feedback/LoadingState';
import { ErrorState } from '@/shared/components/feedback/ErrorState';

export function BrandProfilePage() {
  const { data: profile, isLoading, isError, error, refetch } = useBrandProfile();
  const autosave = useBrandProfileAutosave(profile);

  const formErrors = autosave.values ? validateBrandProfile(autosave.values) : {};
  const audienceError = formErrors.target_audience;

  if (isLoading) {
    return <LoadingState message="Loading brand profile…" />;
  }

  if (isError || !profile) {
    return (
      <ErrorState
        error={error instanceof Error ? error : 'Failed to load brand profile'}
        onRetry={() => void refetch()}
      />
    );
  }

  if (!autosave.values) {
    return <LoadingState message="Preparing editor…" />;
  }

  return (
    <div className="animate-fade-up space-y-8">
      <header className="flex flex-col gap-4 border-b border-border/60 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p className="text-xs font-medium uppercase tracking-widest text-accent">Brand Memory</p>
          <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
            Personalization settings
          </h1>
          <p className="mt-2 max-w-2xl text-sm text-slate-400">
            Tune voice, tone, audience, and writing samples. Hook Lab uses this context on every
            generation — changes autosave.
          </p>
        </div>
        <div className="flex flex-col items-start gap-3 sm:items-end">
          <SaveStatusBadge
            status={autosave.saveStatus}
            error={autosave.saveError}
            memoryVersion={profile.memory_version}
          />
          <div className="flex gap-2">
            <Button type="button" variant="secondary" onClick={() => autosave.flushSave()}>
              Save now
            </Button>
            <Link to="/generate">
              <Button type="button" variant="ghost">
                Test in Hook Lab →
              </Button>
            </Link>
          </div>
        </div>
      </header>

      {hasBrandFormErrors(formErrors) && (
        <p className="rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-sm text-amber-200">
          Some fields need attention before personalization is fully effective.
        </p>
      )}

      <BrandProfileForm
        profileId={profile.id}
        values={autosave.values}
        onChange={autosave.setValues}
        disabled={autosave.isSaving}
        audienceError={audienceError}
      />
    </div>
  );
}

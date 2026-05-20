import { SettingsSection } from '@/features/brand/components/settings/SettingsSection';
import { TagListEditor } from '@/features/brand/components/settings/TagListEditor';
import type { AudienceProfile } from '@/features/brand/types/brand-profile.types';
import { Textarea } from '@/shared/components/ui/Textarea';

type Props = {
  value: AudienceProfile;
  onChange: (value: AudienceProfile) => void;
  disabled?: boolean;
  error?: string;
};

export function AudienceProfileEditor({ value, onChange, disabled, error }: Props) {
  return (
    <SettingsSection
      title="Target audience"
      description="Who you write for — used for audience-fit scoring and hook personalization."
    >
      <Textarea
        label="Audience summary"
        value={value.summary}
        onChange={(e) => onChange({ ...value, summary: e.target.value })}
        disabled={disabled}
        error={error}
        placeholder="e.g. B2B founders scaling from $1M–$10M ARR"
        minRows={2}
      />
      <div className="mt-6 grid gap-6 lg:grid-cols-3">
        <TagListEditor
          label="Segments"
          items={value.segments}
          onChange={(segments) => onChange({ ...value, segments })}
          disabled={disabled}
          placeholder="founders, RevOps leaders…"
        />
        <TagListEditor
          label="Pain points"
          items={value.pain_points}
          onChange={(pain_points) => onChange({ ...value, pain_points })}
          disabled={disabled}
        />
        <TagListEditor
          label="Goals"
          items={value.goals}
          onChange={(goals) => onChange({ ...value, goals })}
          disabled={disabled}
        />
      </div>
    </SettingsSection>
  );
}

import { BRAND_TONE_OPTIONS } from '@/features/brand/constants/tone-options';
import { SettingsSection } from '@/features/brand/components/settings/SettingsSection';
import { TagListEditor } from '@/features/brand/components/settings/TagListEditor';
import type { ToneProfile } from '@/features/brand/types/brand-profile.types';
import { Input } from '@/shared/components/ui/Input';
import { Select } from '@/shared/components/ui/Select';

type Props = {
  value: ToneProfile;
  onChange: (value: ToneProfile) => void;
  disabled?: boolean;
};

export function ToneProfileEditor({ value, onChange, disabled }: Props) {
  return (
    <SettingsSection
      title="Tone profile"
      description="Primary tone and traits shape how Hook Lab scores and generates opening lines."
    >
      <div className="grid gap-5 sm:grid-cols-2">
        <Select
          label="Primary tone"
          value={value.primary}
          onChange={(e) => onChange({ ...value, primary: e.target.value })}
          disabled={disabled}
          options={[...BRAND_TONE_OPTIONS]}
        />
        <Input
          label="Formality (0–1)"
          type="number"
          min={0}
          max={1}
          step={0.1}
          value={value.formality ?? ''}
          onChange={(e) =>
            onChange({
              ...value,
              formality: e.target.value === '' ? null : Number(e.target.value),
            })
          }
          disabled={disabled}
          hint="Optional — higher = more formal"
        />
        <Input
          label="Energy (0–1)"
          type="number"
          min={0}
          max={1}
          step={0.1}
          value={value.energy ?? ''}
          onChange={(e) =>
            onChange({
              ...value,
              energy: e.target.value === '' ? null : Number(e.target.value),
            })
          }
          disabled={disabled}
          hint="Optional — higher = more energetic"
        />
      </div>
      <div className="mt-6 grid gap-6 lg:grid-cols-2">
        <TagListEditor
          label="Tone traits"
          hint="e.g. direct, witty, data-driven"
          items={value.traits}
          onChange={(traits) => onChange({ ...value, traits })}
          disabled={disabled}
        />
        <TagListEditor
          label="Avoid tones"
          hint="e.g. corporate, salesy"
          items={value.avoid}
          onChange={(avoid) => onChange({ ...value, avoid })}
          disabled={disabled}
        />
      </div>
    </SettingsSection>
  );
}

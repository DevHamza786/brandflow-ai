import { SettingsSection } from '@/features/brand/components/settings/SettingsSection';
import { TagListEditor } from '@/features/brand/components/settings/TagListEditor';
import type { StyleGuidelines } from '@/features/brand/types/brand-profile.types';
import { Input } from '@/shared/components/ui/Input';
import { Textarea } from '@/shared/components/ui/Textarea';

type Props = {
  value: StyleGuidelines;
  onChange: (value: StyleGuidelines) => void;
  disabled?: boolean;
};

export function StyleGuidelinesEditor({ value, onChange, disabled }: Props) {
  return (
    <SettingsSection
      title="Style guidelines"
      description="Do / don't rules and constraints for hook length and formatting."
    >
      <Textarea
        label="Summary"
        value={value.summary}
        onChange={(e) => onChange({ ...value, summary: e.target.value })}
        disabled={disabled}
        minRows={2}
        placeholder="Short, punchy hooks. No jargon. Lead with outcomes."
      />
      <div className="mt-6 grid gap-6 lg:grid-cols-2">
        <TagListEditor
          label="Do"
          items={value.do}
          onChange={(doList) => onChange({ ...value, do: doList })}
          disabled={disabled}
        />
        <TagListEditor
          label="Don't"
          items={value.dont}
          onChange={(dontList) => onChange({ ...value, dont: dontList })}
          disabled={disabled}
        />
      </div>
      <div className="mt-6 grid gap-5 sm:grid-cols-2">
        <Input
          label="Max hook length"
          type="number"
          min={40}
          max={400}
          value={value.max_hook_length ?? ''}
          onChange={(e) =>
            onChange({
              ...value,
              max_hook_length: e.target.value === '' ? null : Number(e.target.value),
            })
          }
          disabled={disabled}
        />
        <label className="flex items-center gap-3 pt-6 text-sm text-slate-300">
          <input
            type="checkbox"
            className="h-4 w-4 rounded border-border bg-surface-overlay accent-accent"
            checked={value.use_emojis === true}
            onChange={(e) =>
              onChange({
                ...value,
                use_emojis: e.target.checked ? true : e.target.indeterminate ? null : false,
              })
            }
            disabled={disabled}
          />
          Allow emojis in hooks
        </label>
      </div>
    </SettingsSection>
  );
}

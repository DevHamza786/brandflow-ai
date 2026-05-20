import { SettingsSection } from '@/features/brand/components/settings/SettingsSection';
import { TagListEditor } from '@/features/brand/components/settings/TagListEditor';

type Props = {
  ctas: string[];
  hookPatterns: string[];
  onCtasChange: (ctas: string[]) => void;
  onHookPatternsChange: (patterns: string[]) => void;
  disabled?: boolean;
};

export function CTASettingsEditor({
  ctas,
  hookPatterns,
  onCtasChange,
  onHookPatternsChange,
  disabled,
}: Props) {
  return (
    <SettingsSection
      title="CTAs & hook patterns"
      description="Preferred calls-to-action and opening patterns the model should favor."
    >
      <div className="grid gap-6 lg:grid-cols-2">
        <TagListEditor
          label="Preferred CTAs"
          items={ctas}
          onChange={onCtasChange}
          disabled={disabled}
          placeholder='DM me "scale"'
        />
        <TagListEditor
          label="Hook patterns"
          items={hookPatterns}
          onChange={onHookPatternsChange}
          disabled={disabled}
          placeholder="contrarian claim, question hook"
        />
      </div>
    </SettingsSection>
  );
}

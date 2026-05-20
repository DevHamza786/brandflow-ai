import { SettingsSection } from '@/features/brand/components/settings/SettingsSection';
import { TagListEditor } from '@/features/brand/components/settings/TagListEditor';

type Props = {
  phrases: string[];
  onChange: (phrases: string[]) => void;
  disabled?: boolean;
};

export function BannedPhrasesEditor({ phrases, onChange, disabled }: Props) {
  return (
    <SettingsSection
      title="Banned phrases"
      description="Hook Lab will strip these from generated variants — add terms you never want in your voice."
    >
      <TagListEditor
        label="Never use"
        hint='Press Enter after each phrase — e.g. "game-changing", "synergy"'
        items={phrases}
        onChange={onChange}
        disabled={disabled}
        placeholder="game-changing"
      />
    </SettingsSection>
  );
}

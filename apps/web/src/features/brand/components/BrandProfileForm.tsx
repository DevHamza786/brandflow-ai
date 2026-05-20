import { AudienceProfileEditor } from '@/features/brand/components/editors/AudienceProfileEditor';
import { BannedPhrasesEditor } from '@/features/brand/components/editors/BannedPhrasesEditor';
import { CTASettingsEditor } from '@/features/brand/components/editors/CTASettingsEditor';
import { StyleGuidelinesEditor } from '@/features/brand/components/editors/StyleGuidelinesEditor';
import { ToneProfileEditor } from '@/features/brand/components/editors/ToneProfileEditor';
import { MemoryPreviewPanel } from '@/features/brand/components/MemoryPreviewPanel';
import { WritingSamplesManager } from '@/features/brand/components/WritingSamplesManager';
import { SettingsSection } from '@/features/brand/components/settings/SettingsSection';
import type { BrandProfileFormValues } from '@/features/brand/types/brand-profile.types';
import { Input } from '@/shared/components/ui/Input';
import { Textarea } from '@/shared/components/ui/Textarea';

type Props = {
  profileId: string;
  values: BrandProfileFormValues;
  onChange: (updater: (prev: BrandProfileFormValues) => BrandProfileFormValues) => void;
  disabled?: boolean;
  audienceError?: string;
};

export function BrandProfileForm({
  profileId,
  values,
  onChange,
  disabled,
  audienceError,
}: Props) {
  const patch = <K extends keyof BrandProfileFormValues>(
    key: K,
    value: BrandProfileFormValues[K],
  ) => onChange((prev) => ({ ...prev, [key]: value }));

  return (
    <div className="space-y-6">
      <SettingsSection
        title="Brand voice"
        description="The core voice statement injected into Hook Lab prompts."
      >
        <div className="grid gap-5">
          <Input
            label="Profile name"
            value={values.name}
            onChange={(e) => patch('name', e.target.value)}
            disabled={disabled}
          />
          <Textarea
            label="Brand voice"
            value={values.brand_voice}
            onChange={(e) => patch('brand_voice', e.target.value)}
            disabled={disabled}
            minRows={3}
            placeholder="Blunt, high-conviction, no fluff. Speak to operators who ship weekly."
          />
        </div>
      </SettingsSection>

      <ToneProfileEditor
        value={values.tone_profile}
        onChange={(tone_profile) => patch('tone_profile', tone_profile)}
        disabled={disabled}
      />

      <AudienceProfileEditor
        value={values.target_audience}
        onChange={(target_audience) => patch('target_audience', target_audience)}
        disabled={disabled}
        error={audienceError}
      />

      <BannedPhrasesEditor
        phrases={values.banned_phrases}
        onChange={(banned_phrases) => patch('banned_phrases', banned_phrases)}
        disabled={disabled}
      />

      <CTASettingsEditor
        ctas={values.preferred_ctas}
        hookPatterns={values.preferred_hook_patterns}
        onCtasChange={(preferred_ctas) => patch('preferred_ctas', preferred_ctas)}
        onHookPatternsChange={(preferred_hook_patterns) =>
          patch('preferred_hook_patterns', preferred_hook_patterns)
        }
        disabled={disabled}
      />

      <StyleGuidelinesEditor
        value={values.style_guidelines}
        onChange={(style_guidelines) => patch('style_guidelines', style_guidelines)}
        disabled={disabled}
      />

      <WritingSamplesManager profileId={profileId} />

      <MemoryPreviewPanel profileId={profileId} />
    </div>
  );
}

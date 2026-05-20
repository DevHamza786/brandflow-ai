import { useState, type KeyboardEvent } from 'react';
import { Button } from '@/shared/components/ui/Button';
import { Input } from '@/shared/components/ui/Input';
import { cn } from '@/shared/lib/cn';

type Props = {
  label: string;
  hint?: string;
  items: string[];
  onChange: (items: string[]) => void;
  placeholder?: string;
  maxItems?: number;
  disabled?: boolean;
};

export function TagListEditor({
  label,
  hint,
  items: itemsProp,
  onChange,
  placeholder = 'Type and press Enter',
  maxItems = 30,
  disabled,
}: Props) {
  const items = itemsProp ?? [];
  const [draft, setDraft] = useState('');

  const addItem = (raw: string) => {
    const value = raw.trim();
    if (!value || items.includes(value) || items.length >= maxItems) return;
    onChange([...items, value]);
    setDraft('');
  };

  const onKeyDown = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      addItem(draft);
    }
  };

  return (
    <div className="space-y-3">
      <Input
        label={label}
        hint={hint}
        value={draft}
        onChange={(e) => setDraft(e.target.value)}
        onKeyDown={onKeyDown}
        placeholder={placeholder}
        disabled={disabled}
      />
      <div className="flex flex-wrap gap-2">
        {items.map((item) => (
          <span
            key={item}
            className={cn(
              'inline-flex items-center gap-1.5 rounded-full border border-border bg-surface-overlay px-3 py-1 text-xs text-slate-200',
              disabled && 'opacity-60',
            )}
          >
            {item}
            {!disabled && (
              <button
                type="button"
                className="text-slate-500 transition-colors hover:text-red-400"
                onClick={() => onChange(items.filter((i) => i !== item))}
                aria-label={`Remove ${item}`}
              >
                ×
              </button>
            )}
          </span>
        ))}
        {items.length === 0 && (
          <span className="text-xs text-slate-500">No items yet — add your first above.</span>
        )}
      </div>
      {!disabled && draft.trim() && (
        <Button type="button" variant="ghost" className="!py-1.5 !px-3 text-xs" onClick={() => addItem(draft)}>
          Add “{draft.trim()}”
        </Button>
      )}
    </div>
  );
}

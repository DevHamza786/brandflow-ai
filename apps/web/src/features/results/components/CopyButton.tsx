import { useState } from 'react';
import { copyToClipboard } from '@/features/results/lib/copyToClipboard';
import { Button } from '@/shared/components/ui/Button';
import { useToast } from '@/shared/providers/ToastProvider';
import { cn } from '@/shared/lib/cn';

type Props = {
  text: string;
  label?: string;
  className?: string;
  variant?: 'primary' | 'secondary' | 'ghost';
};

export function CopyButton({ text, label = 'Copy', className, variant = 'ghost' }: Props) {
  const toast = useToast();
  const [copied, setCopied] = useState(false);

  const handleCopy = async () => {
    const ok = await copyToClipboard(text);
    if (ok) {
      setCopied(true);
      toast.push('Copied to clipboard', 'success');
      window.setTimeout(() => setCopied(false), 2000);
    } else {
      toast.push('Could not copy — try selecting text manually', 'error');
    }
  };

  return (
    <Button
      type="button"
      variant={variant}
      className={cn('text-xs', className)}
      onClick={() => void handleCopy()}
    >
      {copied ? 'Copied' : label}
    </Button>
  );
}

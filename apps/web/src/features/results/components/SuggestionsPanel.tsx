import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { cn } from '@/shared/lib/cn';

type Props = {
  suggestions: string[];
  className?: string;
};

export function SuggestionsPanel({ suggestions, className }: Props) {
  if (suggestions.length === 0) return null;

  return (
    <Card className={cn('border-accent/20 transition-opacity duration-300', className)}>
      <CardHeader>
        <h3 className="text-sm font-semibold text-slate-200">AI suggestions</h3>
        <p className="mt-1 text-xs text-slate-500">Actionable improvements for your opening lines</p>
      </CardHeader>
      <CardBody>
        <ul className="space-y-3">
          {suggestions.map((suggestion, index) => (
            <li
              key={`${index}-${suggestion.slice(0, 24)}`}
              className="flex gap-3 rounded-lg border border-border/60 bg-surface-overlay/50 px-4 py-3 text-sm leading-relaxed text-slate-300 transition-colors hover:border-accent/25"
            >
              <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent/15 text-xs font-semibold text-accent">
                {index + 1}
              </span>
              <span>{suggestion}</span>
            </li>
          ))}
        </ul>
      </CardBody>
    </Card>
  );
}

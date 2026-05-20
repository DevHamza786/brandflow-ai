import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { Button } from '@/shared/components/ui/Button';

type Props = {
  minConfidence: number;
  onApply: (value: number) => void;
  applying?: boolean;
};

export function ConfidenceThresholdControl({ minConfidence, onApply, applying }: Props) {
  const presets = [0.55, 0.65, 0.75, 0.85];

  return (
    <Card className="border-amber-500/20">
      <CardHeader>
        <h2 className="text-sm font-medium text-slate-200">Confidence threshold</h2>
        <p className="mt-1 text-xs text-slate-500">
          Prevents reckless automation — decisions below threshold are blocked.
        </p>
      </CardHeader>
      <CardBody className="space-y-4">
        <p className="font-mono text-2xl text-white">{(minConfidence * 100).toFixed(0)}%</p>
        <div className="flex flex-wrap gap-2">
          {presets.map((p) => (
            <Button
              key={p}
              variant={Math.abs(p - minConfidence) < 0.01 ? 'primary' : 'secondary'}
              className="text-xs"
              onClick={() => onApply(p)}
              loading={applying}
            >
              {(p * 100).toFixed(0)}%
            </Button>
          ))}
        </div>
      </CardBody>
    </Card>
  );
}

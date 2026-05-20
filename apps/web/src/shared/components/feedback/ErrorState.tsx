import { Button } from '@/shared/components/ui/Button';
import { Card, CardBody } from '@/shared/components/ui/Card';
import type { ApiError } from '@/shared/types/api';

export function ErrorState({
  error,
  onRetry,
}: {
  error: ApiError | Error | string;
  onRetry?: () => void;
}) {
  const message = getErrorMessage(error);

  return (
    <Card className="border-red-500/30">
      <CardBody className="space-y-3">
        <p className="text-sm font-medium text-red-300">Error</p>
        <p className="text-sm text-slate-400">{message}</p>
        {onRetry && (
          <Button variant="secondary" onClick={onRetry}>
            Try again
          </Button>
        )}
      </CardBody>
    </Card>
  );
}

function getErrorMessage(error: ApiError | Error | string): string {
  if (typeof error === 'string') return error;
  if (error instanceof Error) return error.message;
  return error.message;
}

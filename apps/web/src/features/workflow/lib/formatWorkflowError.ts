export function formatWorkflowError(error: Record<string, unknown> | null | undefined): string {
  if (!error || typeof error !== 'object') {
    return 'The workflow failed without additional details.';
  }

  if (typeof error.message === 'string') return error.message;
  if (typeof error.detail === 'string') return error.detail;
  if (typeof error.title === 'string') return error.title;

  try {
    const json = JSON.stringify(error, null, 2);
    return json.length > 280 ? `${json.slice(0, 280)}…` : json;
  } catch {
    return 'The workflow failed.';
  }
}

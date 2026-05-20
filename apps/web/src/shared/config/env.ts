function required(key: keyof ImportMetaEnv, fallback?: string): string {
  const value = import.meta.env[key] ?? fallback;
  if (!value) {
    throw new Error(`Missing environment variable: ${key}`);
  }
  return value;
}

export const env = {
  apiBaseUrl: required('VITE_API_BASE_URL', '/api/v1'),
  workspaceId: required('VITE_DEFAULT_WORKSPACE_ID'),
  defaultContentVersionId: required('VITE_DEFAULT_CONTENT_VERSION_ID'),
  pollIntervalMs: Number(import.meta.env.VITE_POLL_INTERVAL_MS ?? 2000),
} as const;

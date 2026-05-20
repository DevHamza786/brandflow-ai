/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_BASE_URL: string;
  readonly VITE_API_PROXY_TARGET: string;
  readonly VITE_DEFAULT_WORKSPACE_ID: string;
  readonly VITE_DEFAULT_CONTENT_VERSION_ID: string;
  readonly VITE_POLL_INTERVAL_MS: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}

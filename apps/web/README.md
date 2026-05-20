# BrandFlow AI — Web

Vite + React + TypeScript frontend for PBOS hook workflows.

## Setup

```bash
cp .env.example .env
npm install
npm run dev
```

Open http://localhost:5173

`VITE_DEFAULT_WORKSPACE_ID` must match a row in the API database (`workspaces`). Docker API seeds a default dev workspace when `SEED_DEV_DEFAULT_WORKSPACE=true` (see `apps/api/.env.docker.example`). Otherwise run: `php artisan db:seed --class=DevDefaultWorkspaceSeeder`.

## Routes

| Path | Page |
|------|------|
| `/generate` | Start hook generation |
| `/integrations/posts` | LinkedIn publish queue & posted activity |
| `/runs/:id` | Workflow status (polling) |
| `/results/:id` | Normalized results viewer |

## Architecture

```
src/
  app/          # Router, QueryClient, providers
  features/     # Feature hooks + components
  pages/        # Route pages (thin)
  shared/       # API, UI primitives, config
  services/     # API re-exports
```

API requests proxy to Laravel via `VITE_API_PROXY_TARGET` (default `http://localhost:8080`).

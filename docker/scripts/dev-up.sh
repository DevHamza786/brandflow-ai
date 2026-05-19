#!/usr/bin/env bash
# Start PBOS Docker development stack.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT}"

if [[ ! -f .env ]]; then
  echo "Creating .env from .env.docker.example..."
  cp .env.docker.example .env
fi

if [[ ! -f apps/api/.env ]]; then
  echo "Creating apps/api/.env from apps/api/.env.docker.example..."
  cp apps/api/.env.docker.example apps/api/.env
fi

docker compose --profile tools build
docker compose --profile tools up -d

echo ""
echo "PBOS stack is starting."
echo "  API:      http://localhost:${NGINX_PORT:-8080}"
echo "  Horizon:  http://localhost:${NGINX_PORT:-8080}/horizon"
echo "  pgAdmin:  http://localhost:${PGADMIN_PORT:-5050}  (profile: tools)"
echo ""
echo "Logs: docker compose logs -f api horizon scheduler"

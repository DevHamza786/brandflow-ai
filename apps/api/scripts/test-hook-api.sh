#!/usr/bin/env bash
set -euo pipefail

WORKSPACE_ID="${1:-9bb59c64-347a-48b3-b010-e41b5cdc6f4d}"
VERSION_ID="${2:-52686c43-b35d-4d23-a271-562d14632d02}"

curl -s -w "\nHTTP:%{http_code}\n" -X POST \
  "http://nginx/api/v1/content-versions/${VERSION_ID}/hooks/generate" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-Workspace-Id: ${WORKSPACE_ID}" \
  -d '{"options":{"max_variants":3,"target_audience":"B2B founders"}}'

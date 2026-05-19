#!/usr/bin/env bash
# Wait for PostgreSQL and Redis before Laravel bootstrap.
set -euo pipefail

wait_for() {
  local host="$1"
  local port="$2"
  local name="$3"
  local max_attempts="${4:-60}"
  local attempt=1

  echo "[wait] Waiting for ${name} at ${host}:${port}..."

  while ! (echo > /dev/tcp/"${host}"/"${port}") >/dev/null 2>&1; do
    if [[ "${attempt}" -ge "${max_attempts}" ]]; then
      echo "[wait] ERROR: ${name} not available after ${max_attempts} attempts."
      exit 1
    fi
    attempt=$((attempt + 1))
    sleep 1
  done

  echo "[wait] ${name} is up."
}

DB_HOST="${DB_HOST:-postgres}"
DB_PORT="${DB_PORT:-5432}"
REDIS_HOST="${REDIS_HOST:-redis}"
REDIS_PORT="${REDIS_PORT:-6379}"

wait_for "${DB_HOST}" "${DB_PORT}" "PostgreSQL"
wait_for "${REDIS_HOST}" "${REDIS_PORT}" "Redis"

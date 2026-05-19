#!/usr/bin/env bash
set -euo pipefail

/usr/local/bin/pbos-entrypoint.sh php artisan schedule:work

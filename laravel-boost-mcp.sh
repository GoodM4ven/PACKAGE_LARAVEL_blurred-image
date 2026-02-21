#!/usr/bin/env bash
set -e

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"

if [ -x "${SCRIPT_DIR}/workbench/artisan" ]; then
  exec php "${SCRIPT_DIR}/workbench/artisan" boost:mcp "$@"
elif [ -x "${SCRIPT_DIR}/artisan" ]; then
  exec php "${SCRIPT_DIR}/artisan" boost:mcp "$@"
elif [ -x "${SCRIPT_DIR}/vendor/bin/testbench" ]; then
  exec "${SCRIPT_DIR}/vendor/bin/testbench" boost:mcp "$@"
else
  echo "Error: no boost:mcp entrypoint found in ${SCRIPT_DIR}" >&2
  exit 1
fi

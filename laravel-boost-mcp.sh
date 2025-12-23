#!/usr/bin/env bash
set -e

if [ -x "./artisan" ]; then
  exec php artisan boost:mcp "$@"
elif [ -x "./workbench/artisan" ]; then
  exec php workbench/artisan boost:mcp "$@"
elif [ -x "./vendor/bin/testbench" ]; then
  exec ./vendor/bin/testbench boost:mcp "$@"
else
  echo "Error: neither ./artisan nor ./workbench/artisan nor ./vendor/bin/testbench were found" >&2
  exit 1
fi

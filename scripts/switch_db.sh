#!/usr/bin/env bash
# Simple helper to switch DB config in .env for local testing.
# Usage: ./scripts/switch_db.sh sqlite|mysql

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ENV_EXAMPLE="$ROOT_DIR/.env.example"
ENV_FILE="$ROOT_DIR/.env"

if [ ! -f "$ENV_EXAMPLE" ]; then
  echo ".env.example not found. Create it first." >&2
  exit 1
fi

if [ "${1:-}" = "mysql" ]; then
  echo "Switching to MySQL settings in .env"
  # Copy example, then replace DB_DRIVER and uncomment mysql block if present
  cp "$ENV_EXAMPLE" "$ENV_FILE"
  # Replace DB_DRIVER
  sed -i 's/^DB_DRIVER=.*/DB_DRIVER=mysql/' "$ENV_FILE" || true
  echo "Remember to fill DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD in .env"
elif [ "${1:-}" = "sqlite" ] || [ -z "${1:-}" ]; then
  echo "Switching to SQLite settings in .env"
  cp "$ENV_EXAMPLE" "$ENV_FILE"
  sed -i 's/^DB_DRIVER=.*/DB_DRIVER=sqlite/' "$ENV_FILE" || true
  # Ensure default path exists
  mkdir -p "$ROOT_DIR/data"
  touch "$ROOT_DIR/data/hunter_test.sqlite" || true
  echo "Using data/hunter_test.sqlite as DB_DATABASE"
else
  echo "Usage: $0 sqlite|mysql" >&2
  exit 2
fi

echo ".env updated -> $ENV_FILE"

#!/usr/bin/env bash

DEV_SCRIPTS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$DEV_SCRIPTS_DIR/../.." && pwd)"

if [[ ! -f "$REPO_ROOT/.env" ]]; then
    echo "Error: .env file not found. Run 'cp .env.example .env' to create it."
    exit 1
fi

set -a
source "$REPO_ROOT/.env"
set +a

if [[ "$CACHE_PATH" != /* ]]; then
    CACHE_PATH="$REPO_ROOT/$CACHE_PATH"
fi

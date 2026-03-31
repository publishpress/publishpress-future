#!/usr/bin/env bash
#
# Wrapper for Codeception test runs.
# Auto-starts the test environment when the DB data directory is missing,
# which prevents SQLSTATE[HY000] errno=1018 errors on the first run or
# after composer test:clean-cache.
#
# Usage: tests-run.sh [codecept args...]
#

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/env-init.sh"

CACHE_DB="$CACHE_PATH/db_test"

if [[ ! -d "$CACHE_DB" ]] || [[ -z "$(ls -A "$CACHE_DB" 2>/dev/null)" ]]; then
    echo "Test DB cache not found — starting test environment..."
    (cd "$REPO_ROOT/dev-workspace" && bash ./scripts/server.sh up test)
fi

(cd "$REPO_ROOT" && vendor/bin/codecept run "$@")

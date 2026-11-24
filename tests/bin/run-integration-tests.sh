#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

bash "$ROOT/tests/bin/fix-linux-test-volume-perms.sh"
exec bash "$ROOT/tests/bin/run-codecept-suite.sh" Integration INTEGRATION_WORKERS "$@"

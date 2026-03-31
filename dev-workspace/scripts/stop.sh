#!/usr/bin/env bash

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/env-init.sh"
cd "$REPO_ROOT/dev-workspace"

sh ./scripts/terminal-service-stop.sh

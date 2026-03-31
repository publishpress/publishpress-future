#!/usr/bin/env bash

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/env-init.sh"
cd "$REPO_ROOT/dev-workspace"

DB_EXPORT_FILE=/var/www/html/wp-content/plugins/$PLUGIN_SLUG/tests/Support/Data/dump.sql

bash ./scripts/tests-wp-cli.sh wp_test_cli db import $DB_EXPORT_FILE

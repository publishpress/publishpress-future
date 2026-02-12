#!/usr/bin/env bash

# If not in the `dev-workspace` directory, change to it
if [[ ! $(pwd) =~ .*dev-workspace$ ]]; then
  cd dev-workspace
fi

set -a
source /project/.env
set +a

DB_EXPORT_FILE=/var/www/html/wp-content/plugins/$PLUGIN_SLUG/tests/Support/Data/dump.sql

bash ./scripts/tests-wp-cli.sh wp_test_cli db import $DB_EXPORT_FILE

#!/usr/bin/env bash

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/env-init.sh"
cd "$REPO_ROOT/dev-workspace"

DB_CONTAINER_NAME=${CONTAINER_NAME}_env_db_test
DB_LOGS_FILE="$REPO_ROOT/dev-workspace-cache/logs/db_test/general.log"

run_mysql_query() {
  docker exec -i $DB_CONTAINER_NAME bash -c "mysql -u root -proot -e '$1' 2>&1 | grep  -v \"Using a password\""
}

if [[ $1 == "off" ]]; then
  run_mysql_query "SET GLOBAL general_log = OFF;"
  echo "MySQL general log is disabled."
else
  run_mysql_query "SET GLOBAL general_log = ON;"
  echo "MySQL general log is enabled. Check the logs at ${DB_LOGS_FILE}"
fi
